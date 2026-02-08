<?php

namespace App\Test\Controller;

use App\Entity\Event;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class EventControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $repository;
    private string $path = '/event/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->repository = $this->manager->getRepository(Event::class);

        foreach ($this->repository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Event index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'event[titre]' => 'Testing',
            'event[description]' => 'Testing',
            'event[dateCreation]' => 'Testing',
            'event[dateDebut]' => 'Testing',
            'event[dateFin]' => 'Testing',
            'event[capacite]' => 'Testing',
            'event[inscrits]' => 'Testing',
            'event[image]' => 'Testing',
            'event[categorie]' => 'Testing',
            'event[prix]' => 'Testing',
            'event[lieu]' => 'Testing',
            'event[statut]' => 'Testing',
        ]);

        self::assertResponseRedirects('/sweet/food/');

        self::assertSame(1, $this->getRepository()->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Event();
        $fixture->setTitre('My Title');
        $fixture->setDescription('My Title');
        $fixture->setDateCreation('My Title');
        $fixture->setDateDebut('My Title');
        $fixture->setDateFin('My Title');
        $fixture->setCapacite('My Title');
        $fixture->setInscrits('My Title');
        $fixture->setImage('My Title');
        $fixture->setCategorie('My Title');
        $fixture->setPrix('My Title');
        $fixture->setLieu('My Title');
        $fixture->setStatut('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Event');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Event();
        $fixture->setTitre('Value');
        $fixture->setDescription('Value');
        $fixture->setDateCreation('Value');
        $fixture->setDateDebut('Value');
        $fixture->setDateFin('Value');
        $fixture->setCapacite('Value');
        $fixture->setInscrits('Value');
        $fixture->setImage('Value');
        $fixture->setCategorie('Value');
        $fixture->setPrix('Value');
        $fixture->setLieu('Value');
        $fixture->setStatut('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'event[titre]' => 'Something New',
            'event[description]' => 'Something New',
            'event[dateCreation]' => 'Something New',
            'event[dateDebut]' => 'Something New',
            'event[dateFin]' => 'Something New',
            'event[capacite]' => 'Something New',
            'event[inscrits]' => 'Something New',
            'event[image]' => 'Something New',
            'event[categorie]' => 'Something New',
            'event[prix]' => 'Something New',
            'event[lieu]' => 'Something New',
            'event[statut]' => 'Something New',
        ]);

        self::assertResponseRedirects('/event/');

        $fixture = $this->repository->findAll();

        self::assertSame('Something New', $fixture[0]->getTitre());
        self::assertSame('Something New', $fixture[0]->getDescription());
        self::assertSame('Something New', $fixture[0]->getDateCreation());
        self::assertSame('Something New', $fixture[0]->getDateDebut());
        self::assertSame('Something New', $fixture[0]->getDateFin());
        self::assertSame('Something New', $fixture[0]->getCapacite());
        self::assertSame('Something New', $fixture[0]->getInscrits());
        self::assertSame('Something New', $fixture[0]->getImage());
        self::assertSame('Something New', $fixture[0]->getCategorie());
        self::assertSame('Something New', $fixture[0]->getPrix());
        self::assertSame('Something New', $fixture[0]->getLieu());
        self::assertSame('Something New', $fixture[0]->getStatut());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Event();
        $fixture->setTitre('Value');
        $fixture->setDescription('Value');
        $fixture->setDateCreation('Value');
        $fixture->setDateDebut('Value');
        $fixture->setDateFin('Value');
        $fixture->setCapacite('Value');
        $fixture->setInscrits('Value');
        $fixture->setImage('Value');
        $fixture->setCategorie('Value');
        $fixture->setPrix('Value');
        $fixture->setLieu('Value');
        $fixture->setStatut('Value');

        $this->manager->remove($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/event/');
        self::assertSame(0, $this->repository->count([]));
    }
}
