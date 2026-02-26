<?php

namespace App\Controller\Admin;

use App\Entity\Cours;
use App\Entity\Module;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/qualite')]
final class QualiteContenuAdminController extends AbstractController
{
    #[Route('', name: 'app_admin_qualite_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $now = new \DateTime();

        $coursSansContenu = $entityManager->getRepository(Cours::class)
            ->createQueryBuilder('c')
            ->andWhere('(c.contenu IS NULL OR c.contenu = \'\')')
            ->andWhere('(c.fichierContenu IS NULL OR c.fichierContenu = \'\')')
            ->orderBy('c.dateCreation', 'DESC')
            ->setMaxResults(200)
            ->getQuery()
            ->getResult();

        $qbDuplicates = $entityManager->getRepository(Cours::class)
            ->createQueryBuilder('c')
            ->select('IDENTITY(c.module) AS module_id, c.ordre AS ordre, COUNT(c.id) AS cnt')
            ->groupBy('module_id, ordre')
            ->having('cnt > 1')
            ->orderBy('cnt', 'DESC');

        $duplicateRows = $qbDuplicates->getQuery()->getArrayResult();
        $duplicateOrdres = [];
        foreach ($duplicateRows as $row) {
            $mid = isset($row['module_id']) ? (int) $row['module_id'] : 0;
            $ordre = isset($row['ordre']) ? (int) $row['ordre'] : 0;
            if ($mid > 0) {
                $duplicateOrdres[$mid][] = $ordre;
            }
        }

        $duplicateModuleIds = array_keys($duplicateOrdres);
        $modulesOrdreDuplique = $duplicateModuleIds !== []
            ? $entityManager->getRepository(Module::class)->findBy(['id' => $duplicateModuleIds])
            : [];

        $coursOrdreDuplique = [];
        if ($duplicateOrdres !== []) {
            $orX = [];
            $params = [];
            $i = 0;
            foreach ($duplicateOrdres as $mid => $ordres) {
                foreach ($ordres as $ordre) {
                    $orX[] = sprintf('(IDENTITY(c.module) = :m%d AND c.ordre = :o%d)', $i, $i);
                    $params['m' . $i] = $mid;
                    $params['o' . $i] = $ordre;
                    $i += 1;
                }
            }

            $qb = $entityManager->getRepository(Cours::class)
                ->createQueryBuilder('c')
                ->leftJoin('c.module', 'm')
                ->addSelect('m')
                ->andWhere(implode(' OR ', $orX))
                ->orderBy('m.titre', 'ASC')
                ->addOrderBy('c.ordre', 'ASC');

            foreach ($params as $k => $v) {
                $qb->setParameter($k, $v);
            }

            $coursOrdreDuplique = $qb->getQuery()->getResult();
        }

        $coursVisibiliteFuture = $entityManager->getRepository(Cours::class)
            ->createQueryBuilder('c')
            ->andWhere('c.visible = :visible')
            ->andWhere('c.visibleFrom IS NOT NULL')
            ->andWhere('c.visibleFrom > :now')
            ->setParameter('visible', true)
            ->setParameter('now', $now)
            ->orderBy('c.visibleFrom', 'ASC')
            ->setMaxResults(200)
            ->getQuery()
            ->getResult();

        $modulesSansCours = $entityManager->getRepository(Module::class)
            ->createQueryBuilder('m')
            ->leftJoin('m.cours', 'c')
            ->addSelect('c')
            ->groupBy('m.id')
            ->having('COUNT(c.id) = 0')
            ->orderBy('m.dateCreation', 'DESC')
            ->setMaxResults(200)
            ->getQuery()
            ->getResult();

        return $this->render('admin/qualite/index.html.twig', [
            'cours_sans_contenu' => $coursSansContenu,
            'cours_ordre_duplique' => $coursOrdreDuplique,
            'modules_ordre_duplique' => $modulesOrdreDuplique,
            'cours_visibilite_future' => $coursVisibiliteFuture,
            'modules_sans_cours' => $modulesSansCours,
        ]);
    }

    #[Route('/export', name: 'app_admin_qualite_export', methods: ['POST'])]
    public function export(Request $request, EntityManagerInterface $entityManager): Response
    {
        $token = (string) $request->request->get('_token', '');
        if (!$this->isCsrfTokenValid('qualite_export', $token)) {
            throw $this->createAccessDeniedException('CSRF token invalide.');
        }

        $now = new \DateTime();
        $coursSansContenu = $entityManager->getRepository(Cours::class)
            ->createQueryBuilder('c')
            ->andWhere('(c.contenu IS NULL OR c.contenu = \'\')')
            ->andWhere('(c.fichierContenu IS NULL OR c.fichierContenu = \'\')')
            ->orderBy('c.dateCreation', 'DESC')
            ->getQuery()
            ->getResult();

        $coursVisibiliteFuture = $entityManager->getRepository(Cours::class)
            ->createQueryBuilder('c')
            ->andWhere('c.visible = :visible')
            ->andWhere('c.visibleFrom IS NOT NULL')
            ->andWhere('c.visibleFrom > :now')
            ->setParameter('visible', true)
            ->setParameter('now', $now)
            ->orderBy('c.visibleFrom', 'ASC')
            ->getQuery()
            ->getResult();

        $response = new StreamedResponse(function () use ($coursSansContenu, $coursVisibiliteFuture): void {
            $out = fopen('php://output', 'w');
            if ($out === false) {
                return;
            }

            fputcsv($out, ['type', 'cours_id', 'cours_titre', 'module', 'details']);

            foreach ($coursSansContenu as $c) {
                $moduleTitre = $c->getModule() ? (string) $c->getModule()->getTitre() : '';
                fputcsv($out, ['cours_sans_contenu', (int) $c->getId(), (string) $c->getTitre(), $moduleTitre, '']);
            }

            foreach ($coursVisibiliteFuture as $c) {
                $moduleTitre = $c->getModule() ? (string) $c->getModule()->getTitre() : '';
                $details = $c->getVisibleFrom() ? $c->getVisibleFrom()->format('Y-m-d H:i:s') : '';
                fputcsv($out, ['cours_visibilite_future', (int) $c->getId(), (string) $c->getTitre(), $moduleTitre, $details]);
            }

            fclose($out);
        });

        $fileName = 'qualite-contenus-' . date('Y-m-d_His') . '.csv';
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');

        return $response;
    }

    #[Route('/fix/hide-empty-cours', name: 'app_admin_qualite_fix_hide_empty', methods: ['POST'])]
    public function hideEmptyCours(Request $request, EntityManagerInterface $entityManager): Response
    {
        $token = (string) $request->request->get('_token', '');
        if (!$this->isCsrfTokenValid('qualite_hide_empty', $token)) {
            throw $this->createAccessDeniedException('CSRF token invalide.');
        }

        $coursSansContenu = $entityManager->getRepository(Cours::class)
            ->createQueryBuilder('c')
            ->andWhere('(c.contenu IS NULL OR c.contenu = \'\')')
            ->andWhere('(c.fichierContenu IS NULL OR c.fichierContenu = \'\')')
            ->getQuery()
            ->getResult();

        $count = 0;
        foreach ($coursSansContenu as $c) {
            if ($c->isVisible()) {
                $c->setVisible(false);
                $count += 1;
            }
        }
        $entityManager->flush();

        $this->addFlash('success', sprintf('%d cours masqués (sans contenu).', $count));
        return $this->redirectToRoute('app_admin_qualite_index');
    }

    #[Route('/fix/resequence-module/{id}', name: 'app_admin_qualite_fix_resequence_module', methods: ['POST'])]
    public function resequenceModule(Request $request, ?Module $module, EntityManagerInterface $entityManager): Response
    {
        if ($module === null) {
            return $this->redirectToRoute('app_admin_qualite_index');
        }

        $token = (string) $request->request->get('_token', '');
        if (!$this->isCsrfTokenValid('qualite_resequence_module' . $module->getId(), $token)) {
            throw $this->createAccessDeniedException('CSRF token invalide.');
        }

        $cours = $entityManager->getRepository(Cours::class)
            ->createQueryBuilder('c')
            ->andWhere('c.module = :module')
            ->setParameter('module', $module)
            ->orderBy('c.ordre', 'ASC')
            ->addOrderBy('c.id', 'ASC')
            ->getQuery()
            ->getResult();

        $i = 1;
        foreach ($cours as $c) {
            $c->setOrdre($i);
            $i += 1;
        }
        $entityManager->flush();

        $this->addFlash('success', 'Ordre des cours réorganisé pour le module: ' . $module->getTitre());
        return $this->redirectToRoute('app_admin_qualite_index');
    }
}
