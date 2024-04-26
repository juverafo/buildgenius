<?php

namespace App\Tests\Unit;

use App\Entity\Quiz;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class QuizTest extends KernelTestCase
{
    public function getEntity(): Quiz
    {
        return (new Quiz())
        ->setType('Nouveau Type');
    }
    public function testEntityIsValid(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $quiz = $this->getEntity();

        $errors = $container->get('validator')->validate($quiz);

        $this->assertCount(0, $errors);

    }
    
    public function testInvalidName()
    {
        self::bootKernel();
        $container = static::getContainer();

        $quiz = $this->getEntity();
        $quiz->setType('');

        $errors = $container->get('validator')->validate($quiz);

        $this->assertCount(2, $errors);
    }
}
