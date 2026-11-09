<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Distancia máxima para considerar dos imágenes casi duplicadas
    |--------------------------------------------------------------------------
    |
    | Los hashes perceptuales son cadenas hexadecimales de 64 bits. La distancia
    | de Hamming indica cuántos bits cambian entre dos huellas: cuanto menor es,
    | más parecidas son las imágenes. Estos valores se pueden ajustar si se
    | detectan falsos positivos o duplicados que no se están bloqueando.
    |
    */

    'phash_max_distance' => (int) env('IMAGE_PHASH_MAX_DISTANCE', 5),

    'dhash_max_distance' => (int) env('IMAGE_DHASH_MAX_DISTANCE', 5),

];
