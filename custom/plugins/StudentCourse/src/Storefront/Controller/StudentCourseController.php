<?php declare(strict_types=1);

namespace StudentCourse\Storefront\Controller;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Shopware\Storefront\Page\GenericPageLoaderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class StudentCourseController extends StorefrontController
{
    public function __construct(
        private readonly GenericPageLoaderInterface $genericPageLoader,
        private readonly EntityRepository $courseRepository
    ) {
    }

    #[Route(path: '/student-courses', name: 'frontend.student_course.index', methods: ['GET'])]
    public function index(Request $request, SalesChannelContext $salesChannelContext): Response
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = 10;

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('isActive', true));
        $criteria->setLimit($limit);
        $criteria->setOffset(($page - 1) * $limit);
        $criteria->setTotalCountMode(Criteria::TOTAL_COUNT_MODE_EXACT);

        $courses = $this->courseRepository->search($criteria, $salesChannelContext->getContext());
        $total = $courses->getTotal();
        $pages = max(1, (int) ceil($total / $limit));

        $pageData = $this->genericPageLoader->load($request, $salesChannelContext);

        return $this->renderStorefront(
            '@StudentCourse/storefront/page/student-course/index.html.twig',
            [
                'page' => $pageData,
                'courses' => $courses,
                'currentPage' => $page,
                'totalPages' => $pages,
                'limit' => $limit,
                'total' => $total,
            ]
        );
    }
}
