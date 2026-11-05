<?php

namespace App\Http\Controllers;

use App\Services\FileManager;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FileManagementController extends Controller
{
    public function index(Request $request, FileManager $manager)
    {
        abort_unless($request->user()->isAdmin(), 403);

        return Inertia::render('Admin/Files', [
            'report' => $manager->overview(),
        ]);
    }

    public function clean(Request $request, FileManager $manager)
    {
        abort_unless($request->user()->isAdmin(), 403);

        $result = $manager->clean($request->boolean('strict'));

        $message = sprintf(
            'Limpieza completada: %d archivos duplicados, %d huérfanos y %d registros duplicados eliminados.',
            $result['deleted_duplicate_files'],
            $result['deleted_orphans'],
            $result['merged_publications'] + $result['merged_paintings'],
        );

        return redirect()
            ->route('files.index')
            ->with('success', $message);
    }
}
