<?php
namespace App\Controller;

use DateTime;
use App\Entity\Comment;
use App\Entity\MicroPost;
use App\Form\CommentType;
use App\Form\MicroPostType;
use App\Repository\MicroPostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceUtil;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class MicroPostController extends AbstractController
{
    #[Route('/micro-post', name: 'app_micro_post')]
    public function index(MicroPostRepository $_posts): Response
    {
        // $microPost = new MicroPost();
        // $microPost->setTitle('It comes from controller');
        // $microPost->setText('Hi!');
        // $microPost->setCreated(new DateTime());

        // $microPost = $posts->find(7);
        // $posts->remove($microPost, true);
        $posts=$_posts->findAll();
       // dd($posts);
        return $this->render('micro_post/index.html.twig', [
            'posts' => $posts,
        ]);

    }

  /*   #[Route('/micro-post/{id}', name: 'app_micro_post_show')]
    #[IsGranted(MicroPost::VIEW, 'post')]

    public function showOne( $id ,MicroPostRepository $posts): Response
    {
       
         $post = $posts->find($id);
       
         return $this->render('micro_post/show.html.twig', [
            'post' => $post,
            'comments' => $post->getComments()
        ]);
 
    } */
    #[Route('/micro-post/{id}', name: 'app_micro_post_show')]
    #[IsGranted(MicroPost::VIEW, 'post')] 
    public function showOne(MicroPost $post): Response
    {
        return $this->render('micro_post/show.html.twig', [
            'post' => $post,
            'comments' => $post->getComments()
        ]);
    }

    #[Route('/micro-post/add', name: 'app_micro_post_add' ,priority:2)]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
       $this->denyAccessUnlessGranted(
        'IS_AUTHENTICATED_FULLY'
       );
        $post = new MicroPost();
    
        $form = $this->createForm(MicroPostType::class, $post);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $post->setCreated(new \DateTime());
            $post->setAuthor($this->getUser());
            
            // Persist & Flush using EntityManager
            $entityManager->persist($post);
            $entityManager->flush();
            $this->addFlash('success','post is added');
            return $this->redirectToRoute('app_micro_post');
          
        }
    
        return $this->render('micro_post/add.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/micro-post/{id}/comment', name: 'app_micro_post_add_comment', priority: 2)]
    public function addComment(
        Request $request, 
        EntityManagerInterface $entityManager, 
        MicroPostRepository $microPostRepository, 
        int $id
    ): Response {
        // Fetch the MicroPost by ID
        $post = $microPostRepository->find($id);
    
        if (!$post) {
            throw $this->createNotFoundException('Post not found.');
        }
    
        // Create a new Comment instance
        $comment = new Comment();
        $comment->setPost($post);
        $comment->setAuthor($this->getUser());
        // Create form for the Comment entity
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Persist & flush the new comment
            $entityManager->persist($comment);
            $entityManager->flush();
    
            $this->addFlash('success', 'Comment added successfully');
    
            return $this->redirectToRoute('app_micro_post_show', ['id' => $post->getId()]);
        }
    
        return $this->render('comment/add.html.twig', [
            'form' => $form->createView(),
            'post' => $post,
        ]);
    }
    
    
    #[Route('/micro-post/edit/{id}', name: 'app_micro_post_edit',priority:2)]
public function edit(int $id, Request $request, MicroPostRepository $posts, EntityManagerInterface $entityManager): Response
{
    $post = $posts->find($id);

    if (!$post) {
        throw $this->createNotFoundException('Post not found');
    }

    $form = $this->createForm(MicroPostType::class, $post);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush();
        $this->addFlash('success', 'Post has been updated');
        
        return $this->redirectToRoute('app_micro_post');
    }

    return $this->render('micro_post/edit.html.twig', [
        'form' => $form,
       
    ]);
}



}