<?php

namespace App\Test\Controller;

use App\Entity\Sponsor;
use App\Repository\SponsorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SponsorControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $repository;
    private string $path = '/sponsor/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->repository = $this->manager->getRepository(Sponsor::class);

        foreach ($this->repository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Sponsor index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'sponsor[nom]' => 'Testing',
            'sponsor[description]' => 'Testing',
            'sponsor[logo]' => 'Testing',
            'sponsor[siteWeb]' => 'Testing',
            'sponsor[type]' => 'Testing',
            'sponsor[montant]' => 'Testing',
            'sponsor[dateDebut]' => 'Testing',
            'sponsor[dateFin]' => 'Testing',
            'sponsor[statut]' => 'Testing',
            'sponsor[contactPersonne]' => 'Testing',
            'sponsor[contactEmail]' => 'Testing',
            'sponsor[contactTelephone]' => 'Testing',
            'sponsor[dateCreation]' => 'Testing',
            'sponsor[event]' => 'Testing',
        ]);

        self::assertResponseRedirects('/sweet/food/');

        self::assertSame(1, $this->getRepository()->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Sponsor();
        $fixture->setNom('My Title');
        $fixture->setDescription('My Title');
        $fixture->setLogo('My Title');
        $fixture->setSiteWeb('My Title');
        $fixture->setType('My Title');
        $fixture->setMontant('My Title');
        $fixture->setDateDebut('My Title');
        $fixture->setDateFin('My Title');
        $fixture->setStatut('My Title');
        $fixture->setContactPersonne('My Title');
        $fixture->setContactEmail('My Title');
        $fixture->setContactTelephone('My Title');
        $fixture->setDateCreation('My Title');
        $fixture->setEvent('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Sponsor');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Sponsor();
        $fixture->setNom('Value');
        $fixture->setDescription('Value');
        $fixture->setLogo('Value');
        $fixture->setSiteWeb('Value');
        $fixture->setType('Value');
        $fixture->setMontant('Value');
        $fixture->setDateDebut('Value');
        $fixture->setDateFin('Value');
        $fixture->setStatut('Value');
        $fixture->setContactPersonne('Value');
        $fixture->setContactEmail('Value');
        $fixture->setContactTelephone('Value');
        $fixture->setDateCreation('Value');
        $fixture->setEvent('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'sponsor[nom]' => 'Something New',
            'sponsor[description]' => 'Something New',
            'sponsor[logo]' => 'Something New',
            'sponsor[siteWeb]' => 'Something New',
            'sponsor[type]' => 'Something New',
            'sponsor[montant]' => 'Something New',
            'sponsor[dateDebut]' => 'Something New',
            'sponsor[dateFin]' => 'Something New',
            'sponsor[statut]' => 'Something New',
            'sponsor[contactPersonne]' => 'Something New',
            'sponsor[contactEmail]' => 'Something New',
            'sponsor[contactTelephone]' => 'Something New',
            'sponsor[dateCreation]' => 'Something New',
            'sponsor[event]' => 'Something New',
        ]);

        self::assertResponseRedirects('/sponsor/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getNom());
        self::assertSame('Something New', $fixture[0]->getDescription());
        self::assertSame('Something New', $fixture[0]->getLogo());
        self::assertSame('Something New', $fixture[0]->getSiteWeb());
        self::assertSame('Something New', $fixture[0]->getType());
        self::assertSame('Something New', $fixture[0]->getMontant());
        self::assertSame('Something New', $fixture[0]->getDateDebut());
        self::assertSame('Something New', $fixture[0]->getDateFin());
        self::assertSame('Something New', $fixture[0]->getStatut());
        self::assertSame('Something New', $fixture[0]->getContactPersonne());
        self::assertSame('Something New', $fixture[0]->getContactEmail());
        self::assertSame('Something New', $fixture[0]->getContactTelephone());
        self::assertSame('Something New', $fixture[0]->getDateCreation());
        self::assertSame('Something New', $fixture[0]->getEvent());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Sponsor();
        $fixture->setNom('Value');
        $fixture->setDescription('Value');
        $fixture->setLogo('Value');
        $fixture->setSiteWeb('Value');
        $fixture->setType('Value');
        $fixture->setMontant('Value');
        $fixture->setDateDebut('Value');
        $fixture->setDateFin('Value');
        $fixture->setStatut('Value');
        $fixture->setContactPersonne('Value');
        $fixture->setContactEmail('Value');
        $fixture->setContactTelephone('Value');
        $fixture->setDateCreation('Value');
        $fixture->setEvent('Value');

        $this->manager->remove($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/sponsor/');
        self::assertSame(0, $this->repository->count([]));
    }
}
