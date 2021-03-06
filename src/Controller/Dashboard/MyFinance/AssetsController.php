<?php

namespace App\Controller\Dashboard\MyFinance;

use App\Controller\Traits\DatatableTrait;
use App\Entity\Finance\CommissionPayment;
use App\Entity\Finance\DividendPayment;
use App\Entity\Finance\Investment;
use App\Entity\Person\Investor;
use App\Entity\Resource\Asset;
use App\Service\Finance\AccountService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route(
 *  "/{_locale}/dashboard/my/finance/assets",
 *  name="dashboard_my_finance_assets_",
 *  requirements={"_locale"="%app.supported_locales%"})
 */
class AssetsController extends AbstractController
{
    use DatatableTrait;

    /**
     * @Route("/", name="index")
     */
    public function index(
        EntityManagerInterface $em,
        AccountService $accountService
    ) {
        $user = $this->getUser();

        $userAccount = $user->getAccount();

        if (!$userAccount) {
            $userAccount = $accountService->createUserAccount($user);
            $user->setAccount($userAccount);
            $em->persist($user);
            $em->flush();
        }

        $balance = $userAccount->getBalance();

        return $this->render('dashboard/my_finance/assets/index.html.twig', [
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

        $search = $request->get('search');

        if (!is_array($search)) {
            $search = [
                'basic' => [],
            ];
        }

        $search = $this->searchValues($request);

        /** @var Investor */
        $investor = $em
            ->getRepository(Investor::class)
            ->findOneBy(['user' => $user]);

        if (!$investor) {
            return $this->redirectToRoute('dashboard_index');
        }

        $qb = $em
            ->createQueryBuilder()
            ->select(['e'])
            ->from(Investment::class, 'e')
            ->join('e.transaction', 't')
            ->join('t.user', 'u')
            ->join('e.resource', 'r')
            ->andWhere('u= :user')
            ->andWhere(sprintf('r INSTANCE OF %s', Asset::class))
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
     * @Route("/search_dividend_payment.json", name="search_dividend_payment", methods={"GET"})
     */
    public function searchDividendPayment(
        Request $request,
        EntityManagerInterface $em
    ) {
        $user = $this->getUser();

        $search = $request->get('search');

        if (!is_array($search)) {
            $search = [
                'basic' => [],
            ];
        }

        $search = $this->searchValues($request);

        /** @var Investor */
        $investor = $em
            ->getRepository(Investor::class)
            ->findOneBy(['user' => $user]);

        if (!$investor) {
            return $this->redirectToRoute('dashboard_index');
        }

        $qb = $em
            ->createQueryBuilder()
            ->select(['e'])
            ->from(DividendPayment::class, 'e')
            ->join('e.transaction', 't')
            ->join('t.user', 'u')
            ->join('e.resource', 'r')
            ->andWhere('u= :user')
            ->andWhere(sprintf('r INSTANCE OF %s', Asset::class))
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
            ->andWhere(sprintf('r INSTANCE OF %s', Asset::class))
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
        return $this->render('dashboard/my_finance/assets/view.html.twig', [
            'entity' => $investment,
        ]);
    }

    /**
     * @Route("/{id}/transaction", name="transaction", methods={"POST"})
     */
    public function justToReturn(Investment $investment)
    {
        // if the form was submitted without inform _method (PUT or DELETE), just redirect to view
        return $this->redirectToRoute('dashboard_assets_investments_view', [
            'id' => $investment->getId(),
        ]);
    }
}
