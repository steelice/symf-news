<?php


namespace App\Controller;


use App\Entity\Article;
use App\Entity\Comment;
use App\Form\ArticleType;
use App\Form\CommentType;
use App\Repository\ArticleRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ArticleController
 * @Route("/article")
 */
class ArticleController extends AbstractController
{
    /**
     * Show news list
     *
     * @Route("/list", defaults={"page": 1}, methods={"GET"}, name="article_list")
     * @Route("/list/{page<[1-9]\d*>}", methods={"GET"}, name="article_list_page")
     * @param int $page
     * @param ArticleRepository $articles
     * @return Response
     */
    public function list(int $page, ArticleRepository $articles): Response
    {
        $list = $articles->findLatest($page);
        return $this->render('article/list.html.twig', [
            'articles' => $list,
            'paging_area' => 'article_list_page'
        ]);
    }

    /**
     * Show new from current user
     *
     * @IsGranted("ROLE_USER")
     * @Route("/list/my", defaults={"page": 1}, methods={"GET"}, name="article_list_my")
     * @Route("/list/my/{page<[1-9]\d*>}", methods={"GET"}, name="article_list_my_page")
     * @param int $page
     * @param ArticleRepository $articles
     * @return Response
     */
    public function listMy(int $page, ArticleRepository $articles): Response
    {
        $list = $articles->findLatest($page, $this->getUser());
        return $this->render('article/list.html.twig', [
            'articles' => $list,
            'paging_area' => 'article_list_my_page'
        ]);
    }

    /**
     * View one item
     *
     * @Route("/view/{slug}", methods={"GET"}, name="article_view")
     * @param Article $article
     * @return Response
     */
    public function view(Article $article): Response
    {
        return $this->render('article/view.html.twig', [
            'article' => $article
        ]);
    }

    /**
     * Edit one item
     *
     * @Route("/edit/{slug}",methods={"GET", "POST"}, name="article_edit")
     * @IsGranted("is_my", subject="article", message="article.not.yours")
     * @param Request $request
     * @param Article $article
     * @return RedirectResponse|Response
     */
    public function edit(Request $request, Article $article)
    {
        $form = $this->createForm(ArticleType::class, $article);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'article.updated_successfully');
            return $this->redirectToRoute('article_view', ['slug' => $article->getSlug()]);
        }

        return $this->render('article/edit.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Delete item
     *
     * @Route("/delete/{slug}",methods={"POST"}, name="article_delete")
     * @IsGranted("is_my", subject="article", message="article.not.yours")
     */
    public function delete(Request $request, Article $article): RedirectResponse
    {
        if (!$this->isCsrfTokenValid('delete', $request->request->get('token'))) {
            return $this->redirectToRoute('index');
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($article);
        $em->flush();
        $this->addFlash('success', 'article.deleted');
        return $this->redirectToRoute('article_list_my');
    }

    /**
     * Create item
     *
     * @IsGranted("ROLE_USER")
     * @Route("/create", methods={"GET", "POST"}, name="article_create")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function create(Request $request)
    {
        $post = new Article();
        $post->setUser($this->getUser());
        $form = $this->createForm(ArticleType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($post);
            $em->flush();
            $this->addFlash('success', 'article.created_successfully');
            return $this->redirectToRoute('index');
        }

        return $this->render('article/create.html.twig', [
            'post' => $post,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Comment form part on article
     *
     * @param Article $article
     * @return Response
     */
    public function commentForm(Article $article): Response
    {
        $form = $this->createForm(CommentType::class);

        return $this->render('article/_comment_form.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Creating new comment
     *
     * @Route("/comment/{articleSlug}/new", methods={"POST"}, name="comment_new")
     * @IsGranted("ROLE_USER")
     * @ParamConverter("article", options={"mapping": {"articleSlug": "slug"}})
     * @param Request $request
     * @param Article $article
     * @return Response
     */
    public function commentNew(Request $request, Article $article): Response
    {
        $comment = new Comment();
        $comment->setUser($this->getUser());
        $article->addComment($comment);
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($comment);
            $em->flush();
            return $this->redirectToRoute('article_view', ['slug' => $article->getSlug()]);
        }

        return $this->render('article/comment_form_error.html.twig', [
            'article' => $article,
            'form' => $form->createView(),
        ]);
    }
}