<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Painting;
use App\Models\Publication;
use App\Models\PublicationComment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PublicationSeeder extends Seeder
{
    /**
     * Comentarios genéricos para dar vida a las publicaciones.
     *
     * @var list<string>
     */
    private const COMMENTS = [
        'Muy interesante, sobre todo la parte dedicada a la luz.',
        'Me ha encantado cómo explicas el contexto histórico.',
        '¿Qué obra recomendarías para empezar a explorar este movimiento?',
        'Gran artículo, se nota el cariño por el tema.',
        'Nunca había pensado en la relación entre color y emoción.',
        'Gracias por compartir, me llevo varias ideas para seguir mirando.',
        'La obra relacionada es una elección perfecta.',
        'Un texto claro y ameno, ideal para quienes empiezan.',
        'Me gustaría leer más sobre este movimiento en el futuro.',
        'Totalmente de acuerdo con la reflexión final.',
        'La parte sobre la pincelada me pareció especialmente lúcida.',
        'Voy a volver a ver las obras con otra mirada después de esto.',
        'Qué buen resumen, sin quedarse en lo obvio.',
        'Me encanta cuando un texto invita a mirar más despacio.',
    ];

    /**
     * Cierres comunes a todas las publicaciones.
     *
     * @var list<string>
     */
    private const CLOSINGS = [
        'Mirar con calma suele recompensar: los detalles que al principio parecen secundarios terminan armando el sentido del conjunto.',
        'Quizá la mejor forma de acercarse sea dejar de preguntar qué representa y empezar a preguntar qué nos hace sentir.',
        'Cada época inventa su manera de mirar; estas obras siguen invitándonos a probar la nuestra.',
        'La obra relacionada en la galería es una buena puerta de entrada para seguir explorando el movimiento.',
        'Si algo caracteriza a este movimiento es su capacidad de seguir generando preguntas mucho después de haber sido pintado.',
        'Vale la pena volver sobre estas imágenes: nunca se ven exactamente igual dos veces.',
    ];

    /**
     * Títulos que combinan una obra concreta con su categoría.
     *
     * @var list<string>
     */
    private const PAINTING_TITLES = [
        '«:obra»: una mirada al :categoria',
        'Detrás de «:obra»: proceso y símbolo',
        ':categoria a través de «:obra»',
    ];

    public function run(): void
    {
        $users = User::query()->orderBy('id')->get();

        if ($users->isEmpty()) {
            $this->command?->warn('No hay usuarios para generar publicaciones.');

            return;
        }

        $categories = Category::query()->orderBy('name')->get();

        $created = 0;
        $comments = 0;

        DB::transaction(function () use ($users, $categories, &$created, &$comments) {
            foreach ($users as $user) {
                // Idempotente: si el usuario ya tiene publicaciones, no duplicamos.
                if (Publication::query()->where('user_id', $user->id)->exists()) {
                    continue;
                }

                $painting = $user->paintings()->with('category')->inRandomOrder()->first();
                $category = $painting?->category ?? $categories->random();

                $this->createPublication($user, $category, $painting, true, 0);
                $created++;

                $extraChance = $painting ? 70 : 25;

                if (random_int(1, 100) <= $extraChance) {
                    $isPublished = random_int(1, 100) > 25;

                    $comments += $this->createPublication(
                        $user,
                        $category,
                        $painting,
                        $isPublished,
                        1,
                    );

                    $created++;
                }
            }
        });

        $this->command?->info("Publicaciones creadas: {$created}");
        $this->command?->info("Comentarios creados: {$comments}");
    }

    private function createPublication(
        User $user,
        ?Category $category,
        ?Painting $painting,
        bool $isPublished,
        int $variant,
    ): int {
        $slug = $category?->slug ?? 'general';
        $data = $this->content()[$slug] ?? $this->content()['general'];
        $seed = $user->id + ($variant * 7);

        $titles = $painting
            ? self::PAINTING_TITLES
            : $data['titles'];

        $title = $this->replacePlaceholders(
            $this->pick($titles, $seed),
            $painting,
            $category,
        );

        $summary = $this->replacePlaceholders(
            $this->pick($data['summaries'], $seed + 1),
            $painting,
            $category,
        );

        $intro = $this->pick($data['intro'], $seed + 2);
        $focus = $this->pick($data['focus'], $seed + 3);
        $closing = $this->pick(self::CLOSINGS, $seed + 4);

        if ($painting) {
            $bridge = $this->pick([
                'En «:obra» se puede seguir esta idea de cerca.',
                'La obra «:obra», incluida en la galería, permite verlo en concreto.',
                '«:obra» ofrece un buen ejemplo de todo esto.',
            ], $seed + 5);

            $focus = $this->replacePlaceholders($bridge, $painting, $category).' '.$focus;
        }

        $date = $isPublished
            ? now()->subDays(random_int(5, 330))->subMinutes(random_int(0, 1439))
            : now()->subDays(random_int(1, 120));

        $publication = Publication::create([
            'user_id' => $user->id,
            'category_id' => $category?->id,
            'painting_id' => $painting?->id,
            'title' => $title,
            'summary' => $summary,
            'content' => implode("\n\n", [$intro, $focus, $closing]),
            'is_published' => $isPublished,
            'published_at' => $isPublished ? $date : null,
            'created_at' => $date,
            'updated_at' => $date,
        ]);

        if (! $isPublished) {
            return 0;
        }

        return $this->seedComments($publication, $user);
    }

    private function seedComments(Publication $publication, User $author): int
    {
        $commenters = User::query()
            ->where('id', '!=', $author->id)
            ->inRandomOrder()
            ->take(random_int(1, 3))
            ->get();

        foreach ($commenters as $commenter) {
            $date = $publication->created_at->copy()->addMinutes(random_int(60, 60 * 24 * 15));

            if ($date->greaterThan(now())) {
                $date = now()->subMinutes(random_int(5, 120));
            }

            if ($date->lessThan($publication->created_at)) {
                $date = $publication->created_at->copy()->addMinute();
            }

            PublicationComment::create([
                'user_id' => $commenter->id,
                'publication_id' => $publication->id,
                'body' => $this->pick(self::COMMENTS, $commenter->id + $publication->id),
                'created_at' => $date,
                'updated_at' => $date,
            ]);
        }

        return $commenters->count();
    }

    private function pick(array $items, int $seed): string
    {
        return $items[$seed % count($items)];
    }

    private function replacePlaceholders(string $text, ?Painting $painting, ?Category $category): string
    {
        return str_replace(
            [':obra', ':categoria'],
            [$painting?->title ?? 'la obra', $category?->name ?? 'el arte'],
            $text,
        );
    }

    /**
     * Contenido base por categoría.
     *
     * @return array<string, array{titles: list<string>, summaries: list<string>, intro: list<string>, focus: list<string>}>
     */
    private function content(): array
    {
        return [
            'impresionismo' => [
                'titles' => [
                    'La luz que cambió la pintura: claves del Impresionismo',
                    'Pinceladas sueltas, emociones intactas',
                    'Salir al aire libre: el gesto impresionista',
                ],
                'summaries' => [
                    'Una invitación a mirar la luz y el instante tal como los entendieron los impresionistas.',
                    'Del taller al paisaje: así cambió la pintura cuando salió a respirar.',
                    'La fugacidad convertida en método, color y emoción.',
                ],
                'intro' => [
                    'El Impresionismo supone una de las rupturas más fértiles de la historia del arte: la pintura deja de perseguir el contorno exacto para quedarse con la impresión de un instante.',
                    'Hablar de Impresionismo es hablar de luz, de aire y de tiempo. Los artistas de este movimiento salieron del taller para registrar lo que ocurría ante sus ojos, sin corregir la fugacidad del momento.',
                    'A finales del siglo XIX, un grupo de pintores decidió mirar el mundo de otra manera: menos contorno, más atmósfera; menos detalle, más sensación.',
                ],
                'focus' => [
                    'La pincelada se vuelve visible y el color construye la forma antes que el dibujo; por eso conviene mirar de cerca y luego alejarse unos pasos.',
                    'El paisaje y la vida cotidiana dejan de ser un tema menor y se convierten en el escenario donde la luz hace su trabajo.',
                    'Más que copiar la realidad, estos artistas registran la experiencia de estar frente a ella, con todo lo que tiene de cambiante.',
                ],
            ],

            'cubismo' => [
                'titles' => [
                    'Romper la forma: una introducción al Cubismo',
                    'Varios puntos de vista en un mismo lienzo',
                    'Geometría y tiempo en la pintura cubista',
                ],
                'summaries' => [
                    'Cómo el Cubismo desmontó la mirada única y abrió la puerta a la abstracción.',
                    'Un recorrido por la fragmentación, el plano y la simultaneidad.',
                    'La forma deja de ser una ventana y se convierte en un objeto a estudiar.',
                ],
                'intro' => [
                    'El Cubismo propone algo radical: renunciar a un único punto de vista y mostrar el objeto como un conjunto de miradas posibles.',
                    'Entre 1907 y 1914, la pintura europea aprendió a descomponer la forma, a mezclar planos y a introducir el tiempo dentro del cuadro.',
                    'Si el Impresionismo se preocupó por la luz, el Cubismo se obsesionó con la estructura: qué queda de un objeto cuando lo miramos desde todos lados a la vez.',
                ],
                'focus' => [
                    'Los planos se superponen y los contornos se abren; el ojo debe reconstruir lo que la pintura ya no le entrega de forma directa.',
                    'La paleta se contiene para que la atención se concentre en la construcción, en las relaciones entre volúmenes y vacíos.',
                    'El collage y los materiales cotidianos llevan la reflexión un paso más allá: la obra también puede hablar del mundo que la rodea.',
                ],
            ],

            'surrealismo' => [
                'titles' => [
                    'El sueño como materia: notas sobre el Surrealismo',
                    'Lo imposible también se pinta',
                    'Automatismo, deseo y símbolo en el Surrealismo',
                ],
                'summaries' => [
                    'Cuando el inconsciente se sienta frente al caballete, las reglas del juego cambian.',
                    'Un paseo por los sueños, los símbolos y las imágenes que no se dejan explicar.',
                    'El Surrealismo convirtió lo irracional en un método de creación.',
                ],
                'intro' => [
                    'El Surrealismo no buscó representar el mundo tal como es, sino abrir una puerta a lo que ocurre cuando la razón baja la guardia.',
                    'Influido por el psicoanálisis, el movimiento convirtió el sueño, el deseo y el azar en materiales de trabajo.',
                    'Hay algo de juego y algo de vértigo en estas obras: las imágenes se encadenan con una lógica que no es la de la vigilia.',
                ],
                'focus' => [
                    'Los objetos se encuentran donde no deberían y las escalas se vuelven caprichosas; esa extrañeza es precisamente el punto de partida.',
                    'El automatismo deja que la mano avance antes que el pensamiento, mientras otras obras construyen escenarios oníricos con una precisión casi fotográfica.',
                    'Cada símbolo admite varias lecturas, y esa ambigüedad es parte del placer de mirar.',
                ],
            ],

            'realismo' => [
                'titles' => [
                    'La verdad cotidiana: el Realismo y sus historias',
                    'Pintar lo que está delante',
                    'Oficios, rostros y dignidad en el Realismo',
                ],
                'summaries' => [
                    'Una mirada a la vida corriente convertida en materia pictórica.',
                    'Cuando lo cotidiano deja de ser un detalle y ocupa el centro del cuadro.',
                    'El Realismo y su decisión de contar el mundo sin adornos.',
                ],
                'intro' => [
                    'El Realismo del siglo XIX se propuso mirar la vida tal como era, sin idealizarla ni esconder sus contradicciones.',
                    'Frente a los grandes temas históricos, estos artistas eligieron el trabajo, la calle, la mesa y los rostros anónimos.',
                    'Pintar lo cotidiano también es una toma de posición: supone afirmar que esa vida merece ser contada.',
                ],
                'focus' => [
                    'La composición se vuelve sobria y los detalles materiales importan: la textura de un tejido, el cansancio de un gesto, la luz de una habitación humilde.',
                    'El retrato y la escena de género permiten asomarse a existencias que rara vez ocupaban un museo.',
                    'La verosimilitud no es solo técnica: es una forma de atención y de respeto hacia lo representado.',
                ],
            ],

            'barroco' => [
                'titles' => [
                    'Claroscuro y drama: el pulso del Barroco',
                    'La emoción del Barroco en diez minutos',
                    'Luz, sombra y movimiento en el Barroco',
                ],
                'summaries' => [
                    'El Barroco llevó el contraste, el gesto y la teatralidad al centro de la escena.',
                    'Una aproximación al movimiento que convirtió la luz en dramaturgia.',
                    'Sombra, cuerpo y emoción: las claves de una época intensa.',
                ],
                'intro' => [
                    'El Barroco entiende la pintura como una experiencia intensa: cuerpos en movimiento, luces que recortan y sombras que esconden.',
                    'Surgido en un contexto de tensiones religiosas y políticas, el movimiento utilizó la imagen para conmover antes que para explicar.',
                    'Pocas épocas han confiado tanto en el poder del contraste: un rostro iluminado puede contar una historia entera.',
                ],
                'focus' => [
                    'El claroscuro organiza la mirada y crea jerarquías; lo que queda en penumbra también participa del relato.',
                    'Las composiciones se abren en diagonales y los gestos se exageran para que la escena llegue al espectador con fuerza.',
                    'La materialidad de la pintura, con empastes y texturas, refuerza la sensación de presencia.',
                ],
            ],

            'renacimiento' => [
                'titles' => [
                    'Perspectiva y proporción: el sueño del Renacimiento',
                    'El artista como inventor',
                    'Armonía, medida y belleza en el Renacimiento',
                ],
                'summaries' => [
                    'La búsqueda de un orden visible: perspectiva, proporción y humanismo.',
                    'Cuando el arte quiso explicar el mundo con reglas y medidas.',
                    'El Renacimiento y la confianza en que la belleza podía calcularse.',
                ],
                'intro' => [
                    'El Renacimiento confió en que el mundo podía comprenderse mediante la medida, la proporción y la observación atenta.',
                    'La perspectiva lineal permitió construir espacios verosímiles, mientras el estudio de la anatomía devolvía al cuerpo su protagonismo.',
                    'Más que un estilo, el Renacimiento fue una manera de mirar: el artista dejó de ser un artesano para reclamar el lugar del inventor.',
                ],
                'focus' => [
                    'Las líneas de fuga ordenan la composición y guían la mirada hacia el centro; nada parece dejado al azar.',
                    'El equilibrio entre figura y espacio busca una armonía que también es una idea filosófica.',
                    'El dibujo se convierte en herramienta de conocimiento: medir, comparar, corregir.',
                ],
            ],

            'modernismo' => [
                'titles' => [
                    'Curvas, naturaleza y deseo: el Modernismo',
                    'El detalle como lenguaje decorativo',
                    'Modernismo: cuando el arte se volvió total',
                ],
                'summaries' => [
                    'La línea curva y la naturaleza como respuesta a la rigidez industrial.',
                    'Un movimiento que quiso rodear la vida entera de belleza.',
                    'Del cartel al interior doméstico: el Modernismo como experiencia global.',
                ],
                'intro' => [
                    'El Modernismo reaccionó contra la rigidez de la industrialización con un lenguaje de curvas, flores y materiales sensuales.',
                    'Más que una escuela de pintura, fue una voluntad de rodear la vida cotidiana de belleza: muebles, carteles, joyas y fachadas.',
                    'La línea se vuelve ornamental y orgánica, y el detalle deja de ser un adorno para convertirse en estructura.',
                ],
                'focus' => [
                    'La naturaleza entra en la obra estilizada, con tallos, cabellos y pliegues que parecen crecer desde dentro.',
                    'El color y la textura buscan una sensación de lujo y de intimidad, incluso en soportes modestos.',
                    'La frontera entre arte y diseño se vuelve porosa, y esa es una de las grandes herencias del movimiento.',
                ],
            ],

            'expresionismo' => [
                'titles' => [
                    'El color como grito: claves del Expresionismo',
                    'Pintar desde dentro',
                    'Distorsión y emoción en el Expresionismo',
                ],
                'summaries' => [
                    'Cuando el color deja de describir y empieza a gritar.',
                    'Una pintura que prefiere la emoción a la corrección formal.',
                    'El Expresionismo y la subjetividad llevada al extremo.',
                ],
                'intro' => [
                    'El Expresionismo no quiere reflejar el mundo, sino la sacudida que el mundo produce en quien lo mira.',
                    'Los colores se intensifican, las formas se deforman y la composición se tensa para que la emoción llegue antes que la explicación.',
                    'En un contexto de crisis y transformación, la pintura se convierte en un espacio donde la subjetividad no pide permiso.',
                ],
                'focus' => [
                    'El color se libera de la descripción naturalista y actúa por contraste, como una descarga emocional.',
                    'Rostros y cuerpos se alargan o se quiebran: la distorsión no es un error, es el lenguaje del malestar o del deseo.',
                    'La pincelada rápida y el trazo nervioso dejan ver el proceso, la duda y la urgencia.',
                ],
            ],

            'arte-abstracto' => [
                'titles' => [
                    'Sin figura, con emoción: el arte abstracto',
                    'El color como protagonista',
                    'Abstracción: pintar lo que no se ve',
                ],
                'summaries' => [
                    'Un recorrido por la pintura que renunció a la figura para ganar otros lenguajes.',
                    'Cuando el color, la línea y la materia bastan para contar algo.',
                    'La abstracción y la pregunta por lo que una obra puede significar sin representar.',
                ],
                'intro' => [
                    'El arte abstracto renuncia a la figura para trabajar directamente con color, línea, materia y ritmo.',
                    'Esa renuncia no significa que no haya nada que ver: significa que el significado se construye en la relación entre los elementos.',
                    'La abstracción propone una experiencia más abierta, en la que cada espectador completa la obra desde su propia mirada.',
                ],
                'focus' => [
                    'El color puede avanzar o retroceder, la línea puede sostener o cortar, y la textura introduce una dimensión táctil.',
                    'La composición se vuelve un campo de tensiones: equilibrio, peso, repetición y silencio.',
                    'Al desaparecer la anécdota, queda la presencia física de la pintura y el tiempo que exige mirarla.',
                ],
            ],

            'general' => [
                'titles' => [
                    'Una mirada personal al :categoria',
                    'Notas sobre :categoria',
                    ':categoria: apuntes y preguntas',
                ],
                'summaries' => [
                    'Una reflexión sobre :categoria y su manera de mirar el mundo.',
                    'Apuntes para acercarse a :categoria con otros ojos.',
                    'Ideas, referencias y preguntas alrededor de :categoria.',
                ],
                'intro' => [
                    'Acercarse a :categoria exige tiempo y curiosidad: no todo lo importante está en lo que se ve a primera vista.',
                    'Cada movimiento artístico propone una manera distinta de mirar, y :categoria no es la excepción.',
                    'Hablar de :categoria es hablar de decisiones, contextos y formas de entender la imagen.',
                ],
                'focus' => [
                    'La clave está en detenerse en los detalles y preguntarse por qué están ahí y qué relación guardan entre sí.',
                    'El contexto histórico ayuda, pero la experiencia frente a la obra sigue siendo irreemplazable.',
                    'Conviene comparar, volver y dejar que la imagen trabaje en la memoria antes de cerrar una interpretación.',
                ],
            ],
        ];
    }
}
