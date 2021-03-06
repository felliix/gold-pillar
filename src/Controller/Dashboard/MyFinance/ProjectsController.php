<?php

namespace App\Controller\Dashboard\MyFinance;

use App\Controller\Traits\DatatableTrait;
use App\Controller\Traits\UserTrait;
use App\Entity\Finance\CapitalReturnPayment;
use App\Entity\Finance\CommissionPayment;
use App\Entity\Finance\Investment;
use App\Entity\Finance\ProfitPayment;
use App\Entity\Resource\Project;
use App\Service\Finance\AccountService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(
 *  "/{_locale}/dashboard/my/finance/projects",
 *  name="dashboard_my_finance_projects_",
 *  requirements={"_locale"="%app.supported_locales%"})
 */
class ProjectsController extends AbstractController
{
    use DatatableTrait;
    use UserTrait;

    /**
     * @Route("/", name="index")
     */
    public function index(
        EntityManagerInterface $em,
        AccountService $accountService
    ) {
        $user = $this->getUser();

        $userAccount = $this->getUserAccount(
            $this->getUser(),
            $em,
            $accountService
        );

        $balance = $userAccount->getBalance();

        return $this->render('dashboard/my_finance/projects/index.html.twig', [
            'balance' => $balance,
            'companyTransactions' => $userAccount->getTransactions(),
        ]);
    }

    /**
     * @Route("/search.json", name="search", methods={"GET"})
     */
    public function search(Request $request, EntityManagerInterface $em)
    {
        $user = $this->getUser();

        $qb = $em
            ->createQueryBuilder()
            ->select(['e'])
            ->from(Investment::class, 'e')
            ->join('e.transaction', 't')
            ->join('t.user', 'u')
            ->join('e.resource', 'r')
            ->andWhere('u= :user')
            ->andWhere(sprintf('r INSTANCE OF %s', Project::class))
            ->setParameters([
                'user' => $user,
            ])
            ->orderBy('e.id', 'desc');

        $query = $qb->getQuery();

        return $this->dataTable($request, $query, false, [
            'groups' => ['list'],
        ]);
    }

    /**
     * @Route("/search_return_capital.json", name="search_return_capital", methods={"GET"})
     */
    public function searchReturnOfCapital(
        Request $request,
        EntityManagerInterface $em
    ) {
        $user = $this->getUser();

        $qb = $em
            ->createQueryBuilder()
            ->select(['e'])
            ->from(CapitalReturnPayment::class, 'e')
            ->join('e.transaction', 't')
            ->join('t.user', 'u')
            ->join('e.resource', 'r')
            ->andWhere('u= :user')
            ->andWhere(sprintf('r INSTANCE OF %s', Project::class))
            ->setParameters([
                'user' => $user,
            ])
            ->orderBy('e.id', 'desc');

        $query = $qb->getQuery();

        return $this->dataTable($request, $query, false, [
            'groups' => ['list'],
        ]);
    }

    /**
     * @Route("/search_profit_payment.json", name="search_profit_payment", methods={"GET"})
     */
    public function searchProfitPayment(
        Request $request,
        EntityManagerInterface $em
    ) {
        $user = $this->getUser();

        $qb = $em
            ->createQueryBuilder()
            ->select(['e'])
            ->from(ProfitPayment::class, 'e')
            ->join('e.transaction', 't')
            ->join('t.user', 'u')
            ->join('e.resource', 'r')
            ->andWhere('u= :user')
            ->andWhere(sprintf('r INSTANCE OF %s', Project::class))
            ->setParameters([
                'user' => $user,
            ])
            ->orderBy('e.id', 'desc');

        $query = $qb->getQuery();

        return $this->dataTable($request, $query, false, [
            'groups' => ['list'],
        ]);
    }

    /**
     * @Route("/search_commission.json", name="search_commission", methods={"GET"})
     */
    public function searchComission(
        Request $request,
        EntityManagerInterface $em
    ) {
        $user = $this->getUser();

        $qb = $em
            ->createQueryBuilder()
            ->select(['e'])
            ->from(CommissionPayment::class, 'e')
            ->join('e.transaction', 't')
            ->join('t.user', 'u')
            ->join('e.resource', 'r')
            ->andWhere('u= :user')
            ->andWhere(sprintf('r INSTANCE OF %s', Project::class))
            ->orderBy('e.id', 'desc')
            ->setParameters([
                'user' => $user,
            ]);

        $query = $qb->getQuery();

        return $this->dataTable($request, $query, false, [
            'groups' => ['list'],
        ]);
    }

    /**
     * @Route("/{id}", name="view", methods={"GET"})
     */
    public function view(Request $request, Investment $investment)
    {
        return $this->render('dashboard/my_finance/projects/view.html.twig', [
            'entity' => $investment,
        ]);
    }

    /**
     * @Route("/{id}/transaction", name="transaction", methods={"POST"})
     */
    public function justToReturn(Investment $investment)
    {
        // if the form was submitted without inform _method (PUT or DELETE), just redirect to view
        return $this->redirectToRoute('dashboard_projects_investments_view', [
            'id' => $investment->getId(),
        ]);
    }
}
