<?php

namespace App\Controllers;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use App\Models\Blog;
use App\Models\Comment;
use Respect\Validation\Validator as v;



class BlogsController extends BaseController
{
    private $twig;

    public function __construct()
    {
        // Configurar Twig
        $loader = new FilesystemLoader('../app/Views');
        $this->twig = new Environment($loader);
    }

    public function getData()
    {
        $blogs = Blog::orderBy('created_at', 'desc')->get();
        $comments = Comment::orderBy('created_at', 'desc')->take(5)->get(); // Obtener los últimos 5 comentarios
        $tags = Blog::distinct()->pluck('tags'); // Obtener todos los tags únicos

        $data = [
            'blogs' => $blogs,
            'comments' => $comments,
            'tags' => $tags,
            'auth' => $_SESSION['auth'] ?? false
        ];
        return $data;
    }

    public function indexAction()
    {
        $data = $this->getData();
        echo $this->twig->render('index_view.twig',  [
            'data' => $data,
        ]);
    }

    public function aboutAction()
    {
        $data = $this->getData();
        echo $this->twig->render('about_view.twig',  [
            'data' => $data,
        ]);
    }

    public function addBlogAction()
    {
        if (isset($_POST['submit'])) {
            // Validar los datos del formulario
            $validation = v::key('title', v::notEmpty())
                ->key('tags', v::notEmpty())
                ->key('author', v::notEmpty())
                ->key('description', v::notEmpty());
            try {
                $validation->assert($_POST);

                // Procesar los datos del formulario y agregar el blog
                $image = $_FILES['image'];
                if (empty($image['name'])) {
                    $image['name'] = 'beach.jpg';
                }

                $blog = Blog::create([
                    'title' => $_POST['title'],
                    'author' => $_POST['author'],
                    'blog' => $_POST['description'],
                    'image' => $image['name'],
                    'tags' => $_POST['tags'],
                ]);

                // Mover la imagen a la carpeta de imágenes
                if ($image['error'] === UPLOAD_ERR_OK) {
                    $imageFileName = $image['name'];
                    move_uploaded_file($image['tmp_name'], "../public/img/$imageFileName");
                    $blog->image = $imageFileName;
                    $blog->save();
                }

                // Redireccionar a una página diferente después de agregar el blog
                header("Location: /");
                exit();
            } catch (\Exception $e) {
                // Capturar la excepción de validación y manejarla (puedes mostrar mensajes de error, etc.)
                $errors = $e->getMessage();

                // Renderizar la vista de agregar blog con mensajes de error
                $data = [
                    'errors' => $errors,
                ];

                echo $this->twig->render('addBlog_view.twig',  [
                    'data' => $data,
                ]);
            }
        }

        $data = $this->getData();

        echo $this->twig->render('addBlog_view.twig',  [
            'data' => $data,
        ]);
    }


    public function contactAction()
    {
        $data = $this->getData();
        echo $this->twig->render('contact_view.twig',  [
            'data' => $data,
        ]);
    }

    public function showAction()
    {
        $data = $this->getData();
        $data['blog'] = Blog::find($_GET['id']);
        echo $this->twig->render('show_view.twig',  [
            'data' => $data,
        ]);
    }

    public function addCommentAction($request)
    {
        $validador = v::key('user', v::stringType()->notEmpty())
            ->key('comment', v::stringType()->notEmpty())
            ->key('blog_id', v::intVal()->positive());

        try {
            // Validar los datos del formulario
            $validador->assert($request->getParsedBody());
            echo 'Validación pasada';
            // Crear el comentario usando Mass Assignment
            Comment::create([
                'user' => $request->getParsedBody()['user'],
                'comment' => $request->getParsedBody()['comment'],
                'blog_id' => $request->getParsedBody()['blog_id']
            ]);

            // Redirigir a la vista del blog
            header("Location: /show?id=" . $request->getParsedBody()['blog_id']);
            exit;
        } catch (\Exception $e) {
            // Capturar errores y renderizar la vista con el mensaje de error
            $error = "Error: " . $e->getMessage();
            $data = $this->getData();
            $data['blog'] = Blog::find($request->getParsedBody()['blog_id']);
            $data['error'] = $error;
            echo $this->twig->render('show_view.twig', ['data' => $data]);
        }
    }
}
