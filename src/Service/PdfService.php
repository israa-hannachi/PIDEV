<?php

namespace App\Service;

use App\Entity\Registration;
use App\Entity\Event;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;

class PdfService
{
    private $twig;
    private $requestStack;
    
    public function __construct(Environment $twig, RequestStack $requestStack)
    {
        $this->twig = $twig;
        $this->requestStack = $requestStack;
    }

    public function generateTicketPdf(Registration $registration): string
    {
        // Get the current base URL from the request if possible
        $request = $this->requestStack->getCurrentRequest();
        $baseUrl = $request ? $request->getSchemeAndHttpHost() : 'https://naja7ni-isra.tn';
        
        $qrUrlTarget = $baseUrl . "/registration/" . $registration->getId() . "/view"; 
        
        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($qrUrlTarget) . "&color=0FB5A9";
        
        $qrBase64 = '';
        try {
            $qrContent = file_get_contents($qrUrl);
            if ($qrContent) {
                $qrBase64 = 'data:image/png;base64,' . base64_encode($qrContent);
            }
        } catch (\Exception $e) {
            // Fallback to text if QR fails to fetch
        }

        $html = $this->twig->render('front/events/ticket_pdf.html.twig', [
            'registration' => $registration,
            'event' => $registration->getEvenement(),
            'qr_code' => $qrBase64
        ]);

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');
        
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    public function getTicketFilename(Registration $registration): string
    {
        return sprintf(
            'Ticket_%s_%s.pdf',
            str_replace([' ', '/', '\\'], '_', $registration->getEvenement()->getTitre()),
            $registration->getId()
        );
    }
}
