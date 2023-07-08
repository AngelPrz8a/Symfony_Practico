<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Form\CommentType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PageController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function home(PostRepository $post): Response
    {
        return $this->render('page/home.html.twig', [
            'posts' => $post->findLatest(),
        ]);
    }

    #[Route('/blog/{slug}', name: 'app_post')]
    public function post(Post $post): Response
    {
        $form = $this->createForm(CommentType::class);
        return $this->render('page/post.html.twig', [
            'post' => $post,
            "form"=> $form->createView(),
        ]);
    }

    #[Route('/nuevo-comentario/{slug}', name: 'app_comment_new')]
    public function comment(Request $request, EntityManagerInterface $entityManagerInterface, Post $post): Response
    {

        $comment = new Comment();
        $comment->setUser($this->getUser());
        $comment->setPost($post);

        $form = $this->createForm(CommentType::class,$comment);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $entityManagerInterface->persist($comment);
            $entityManagerInterface->flush();

            return $this->redirectToRoute("app_post",["slug"=>$post->getSlug()]);
        }


        return $this->render('page/post.html.twig', [
            'post' => $post,
            "form"=> $form->createView(),
        ]);
    }
}
