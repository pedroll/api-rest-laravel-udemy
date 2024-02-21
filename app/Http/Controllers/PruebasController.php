<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;

class PruebasController extends Controller
{
    public function index()
    {
        $titulo = 'Titulo';
        $animales = ['perro', 'gato', 'pajaros', 'lelos'];

        return view('pruebas.index', [
            'titulo' => $titulo,
            'animales' => $animales,
        ]);
    }

    public function testOrm()
    {
        // saca todos los registros
        $posts = Post::all();
 /*       echo '<h1>POSTS</h1>';
        foreach ($posts as $post) {
            echo "<h2>{$post->title}</h2>";
            echo "<p><span style=\"color:gray\">{$post->user->name} - {$post->category->name}</span></p> ";
            echo "<p>{$post->content}</p><hr>";
        }*/
        $categories = Category::all();
/*        echo '<h1>Categorias</h1>';
        foreach ($categories as $category) {
            echo "<h2>{$category->name}</h2>";
            foreach ($category->posts as $post) {
                echo "<h3>{$post->title}</h3>";
                echo "<p>{$post->content}</p>";
            }
        }*/

        $users = User::all();

        return view('pruebas.orm', [
            'posts' => $posts,
            'categories' => $categories,
            'users' => $users,
        ]);
    }
}
