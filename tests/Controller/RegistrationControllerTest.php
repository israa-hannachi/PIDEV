<?php

namespace App\Test\Controller;

use App\Entity\Registration;
use App\Repository\RegistrationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegistrationControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $repository;
    private string $path = '/registration/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->repository = $this->manager->getRepository(Registration::class);

        foreach ($this->repository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Registration index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'registration[visitorName]' => 'Testing',
            'registration[visitorEmail]' => 'Testing',
            'registration[dateInscription]' => 'Testing',
            'registration[statut]' => 'Testing',
            'registration[presence]' => 'Testing',
            'registration[modePaiement]' => 'Testing',
            'registration[montantPaye]' => 'Testing',
            'registration[paiementStatut]' => 'Testing',
            'registration[notes]' => 'Testing',
            'registration[evenement]' => 'Testing',
        ]);

        self::assertResponseRedirects('/sweet/food/');

        self::assertSame(1, $this->getRepository()->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Registration();
        $fixture->setVisitorName('My Title');
        $fixture->setVisitorEmail('My Title');
        $fixture->setDateInscription('My Title');
        $fixture->setStatut('My Title');
        $fixture->setPresence('My Title');
        $fixture->setModePaiement('My Title');
        $fixture->setMontantPaye('My Title');
        $fixture->setPaiementStatut('My Title');
        $fixture->setNotes('My Title');
        $fixture->setEvenement('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Registration');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Registration();
        $fixture->setVisitorName('Value');
        $fixture->setVisitorEmail('Value');
        $fixture->setDateInscription('Value');
        $fixture->setStatut('Value');
        $fixture->setPresence('Value');
        $fixture->setModePaiement('Value');
        $fixture->setMontantPaye('Value');
        $fixture->setPaiementStatut('Value');
        $fixture->setNotes('Value');
        $fixture->setEvenement('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'registration[visitorName]' => 'Something New',
            'registration[visitorEmail]' => 'Something New',
            'registration[dateInscription]' => 'Something New',
            'registration[statut]' => 'Something New',
            'registration[presence]' => 'Something New',
            'registration[modePaiement]' => 'Something New',
            'registration[montantPaye]' => 'Something New',
            'registration[paiementStatut]' => 'Something New',
            'registration[notes]' => 'Something New',
            'registration[evenement]' => 'Something New',
        ]);

        self::assertResponseRedirects('/registration/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getVisitorName());
        self::assertSame('Something New', $fixture[0]->getVisitorEmail());
        self::assertSame('Something New', $fixture[0]->getDateInscription());
        self::assertSame('Something New', $fixture[0]->getStatut());
        self::assertSame('Something New', $fixture[0]->getPresence());
        self::assertSame('Something New', $fixture[0]->getModePaiement());
        self::assertSame('Something New', $fixture[0]->getMontantPaye());
        self::assertSame('Something New', $fixture[0]->getPaiementStatut());
        self::assertSame('Something New', $fixture[0]->getNotes());
        self::assertSame('Something New', $fixture[0]->getEvenement());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Registration();
        $fixture->setVisitorName('Value');
        $fixture->setVisitorEmail('Value');
        $fixture->setDateInscription('Value');
        $fixture->setStatut('Value');
        $fixture->setPresence('Value');
        $fixture->setModePaiement('Value');
        $fixture->setMontantPaye('Value');
        $fixture->setPaiementStatut('Value');
        $fixture->setNotes('Value');
        $fixture->setEvenement('Value');

        $this->manager->remove($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/registration/');
        self::assertSame(0, $this->repository->count([]));
    }
}
