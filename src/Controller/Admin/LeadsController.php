<?php

namespace App\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/leads')]
class LeadsController extends AbstractController
{
    private const ITEMS_PER_PAGE = 5;

    #[Route('/', name: 'app_latest_leads', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $page = $request->query->getInt('page', 1);
        $page = max(1, $page);

        // Mock data for latest leads
        $allLeads = [
            ['id' => 1, 'name' => 'Archie Cantones', 'email' => 'arcie.tones@gmail.com', 'proposal' => 'Sent', 'date' => '11/06/2023 10:53', 'status' => 'Completed'],
            ['id' => 2, 'name' => 'Holmes Cherryman', 'email' => 'golms.chan@gmail.com', 'proposal' => 'New', 'date' => '11/06/2023 10:53', 'status' => 'In Progress'],
            ['id' => 3, 'name' => 'Malanie Hanvey', 'email' => 'lanie.nveyn@gmail.com', 'proposal' => 'Sent', 'date' => '11/06/2023 10:53', 'status' => 'Completed'],
            ['id' => 4, 'name' => 'Kenneth Hune', 'email' => 'nneth.une@gmail.com', 'proposal' => 'Returning', 'date' => '11/06/2023 10:53', 'status' => 'Not Interested'],
            ['id' => 5, 'name' => 'Valentine Maton', 'email' => 'alenine.aton@gmail.com', 'proposal' => 'Sent', 'date' => '11/06/2023 10:53', 'status' => 'Completed'],
            ['id' => 6, 'name' => 'John Smith', 'email' => 'john.smith@gmail.com', 'proposal' => 'New', 'date' => '11/06/2023 10:53', 'status' => 'In Progress'],
            ['id' => 7, 'name' => 'Jane Doe', 'email' => 'jane.doe@gmail.com', 'proposal' => 'Sent', 'date' => '11/06/2023 10:53', 'status' => 'Completed'],
            ['id' => 8, 'name' => 'Michael Brown', 'email' => 'michael.brown@gmail.com', 'proposal' => 'New', 'date' => '11/06/2023 10:53', 'status' => 'In Progress'],
            ['id' => 9, 'name' => 'Sarah Johnson', 'email' => 'sarah.johnson@gmail.com', 'proposal' => 'Sent', 'date' => '11/06/2023 10:53', 'status' => 'Completed'],
            ['id' => 10, 'name' => 'David Lee', 'email' => 'david.lee@gmail.com', 'proposal' => 'Returning', 'date' => '11/06/2023 10:53', 'status' => 'Not Interested'],
        ];

        $totalLeads = count($allLeads);
        $totalPages = ceil($totalLeads / self::ITEMS_PER_PAGE);

        $offset = ($page - 1) * self::ITEMS_PER_PAGE;
        $leads = array_slice($allLeads, $offset, self::ITEMS_PER_PAGE);

        $pagination = [
            'currentPageNumber' => $page,
            'pageCount' => $totalPages,
            'itemsPerPage' => self::ITEMS_PER_PAGE,
            'totalItems' => $totalLeads,
        ];

        return $this->render('admin/leads/index.html.twig', [
            'leads' => $leads,
            'pagination' => $pagination,
        ]);
    }
}
