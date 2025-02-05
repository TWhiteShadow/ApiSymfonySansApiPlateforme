<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Editor;
use App\Entity\User;
use App\Entity\VideoGame;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Create Categories
        $categories = [];
        $categoryNames = ['Action', 'Adventure', 'RPG', 'Strategy', 'Sports', 'Simulation'];
        
        foreach ($categoryNames as $name) {
            $category = new Category();
            $category->setName($name);
            $categories[] = $category;
            $manager->persist($category);
        }

        // Create Editors
        $editors = [];
        $editorData = [
            ['Nintendo', 'Japan'],
            ['Electronic Arts', 'United States'],
            ['Ubisoft', 'France'],
            ['CD Projekt Red', 'Poland'],
            ['Square Enix', 'Japan']
        ];

        foreach ($editorData as [$name, $country]) {
            $editor = new Editor();
            $editor->setName($name)
                  ->setCountry($country);
            $editors[] = $editor;
            $manager->persist($editor);
        }

        // Create Video Games
        $gameData = [
            [
                'The Legend of Zelda: Breath of the Wild',
                '2017-03-03',
                'An open-world action-adventure game',
                $editors[0], // Nintendo
                [$categories[0], $categories[1]] // Action, Adventure
            ],
            [
                'FIFA 24',
                '2023-09-29',
                'A football simulation game',
                $editors[1], // EA
                [$categories[4], $categories[5]] // Sports, Simulation
            ],
            [
                'Assassin\'s Creed Valhalla',
                '2020-11-10',
                'An action role-playing game set in the Viking age',
                $editors[2], // Ubisoft
                [$categories[0], $categories[2]] // Action, RPG
            ],
            [
                'The Witcher 3: Wild Hunt',
                '2015-05-19',
                'An open-world action role-playing game',
                $editors[3], // CD Projekt Red
                [$categories[0], $categories[2]] // Action, RPG
            ],
            [
                'Final Fantasy XVI',
                '2023-06-22',
                'An action role-playing game',
                $editors[4], // Square Enix
                [$categories[0], $categories[2]] // Action, RPG
            ]
        ];

        foreach ($gameData as [$title, $releaseDate, $description, $editor, $gameCategories]) {
            $game = new VideoGame();
            $game->setTitle($title)
                 ->setReleaseDate(new \DateTime($releaseDate))
                 ->setDescription($description)
                 ->setEditor($editor);
            
            foreach ($gameCategories as $category) {
                $game->addCategory($category);
            }
            
            $manager->persist($game);
        }

        // Create Users
        $userData = [
            ['admin@example.com', ['ROLE_ADMIN'], 'adminpass'],
            ['user@example.com', ['ROLE_USER'], 'userpass'],
            ['moderator@example.com', ['ROLE_MODERATOR'], 'modpass']
        ];

        foreach ($userData as [$email, $roles, $plainPassword]) {
            $user = new User();
            $user->setEmail($email);
            $user->setRoles($roles);
            
            $hashedPassword = $this->passwordHasher->hashPassword(
                $user,
                $plainPassword
            );
            $user->setPassword($hashedPassword);
            
            $manager->persist($user);
        }

        $manager->flush();
    }
}