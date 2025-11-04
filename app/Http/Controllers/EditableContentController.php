<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EditableContentController extends Controller
{
    /**
     * Exibe conteúdo público (página para visitantes)
     */
    public function show($slug)
    {
        $content = DB::table('editable_contents')
            ->where('slug', $slug)
            ->where('published', true)
            ->first();

        if (!$content) {
            abort(404);
        }

        $display_pdf = '';
        $description = $content->meta_description ?? config('tes_constants.options.meta_description');
        
        // Breadcrumb
        $breadcrumb = [
            ['name' => 'Início', 'url' => url('/')],
            ['name' => $content->title, 'url' => null]
        ];

        return view('front.editable-content', compact('content', 'display_pdf', 'description', 'breadcrumb'));
    }

    /**
     * Mostra formulário de edição (admin)
     */
    public function edit($slug)
    {
        // Verificar se é admin
        if (!auth()->check() || !in_array(auth()->user()->email, config('tes_constants.admins'))) {
            abort(403, 'Acesso negado');
        }

        $content = DB::table('editable_contents')
            ->where('slug', $slug)
            ->first();

        if (!$content) {
            abort(404);
        }

        return view('admin.edit-content', compact('content'));
    }

    /**
     * Atualiza conteúdo (admin)
     */
    public function update(Request $request, $slug)
    {
        // Verificar se é admin
        if (!auth()->check() || !in_array(auth()->user()->email, config('tes_constants.admins'))) {
            abort(403, 'Acesso negado');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'content' => 'required|string',
            'published' => 'boolean'
        ]);

        DB::table('editable_contents')
            ->where('slug', $slug)
            ->update([
                'title' => $validated['title'],
                'meta_description' => $validated['meta_description'] ?? null,
                'content' => $validated['content'],
                'published' => $request->has('published') ? 1 : 0,
                'updated_at' => now()
            ]);

        // Se for conteúdo da home, redireciona para homepage
        if ($slug === 'precedentes-home') {
            return redirect()->route('searchpage')
                ->with('success', 'Conteúdo atualizado com sucesso!');
        }

        // Caso contrário, redireciona para a página do conteúdo
        return redirect()->route('content.show', $slug)
            ->with('success', 'Conteúdo atualizado com sucesso!');
    }
}
