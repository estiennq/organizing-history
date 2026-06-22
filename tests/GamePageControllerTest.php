<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GamePageControllerTest extends WebTestCase
{
    public function testLoadDifficulty(): void
    {
        $client = static::createClient();
        $client->request('POST', '/level/ww1/ww1-1');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'ww1-1');

        $client->request('POST', '/level/ww1/ww1-2');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'ww1-2');
    }

    public function testValidityCheck(): void
    {
        $client = static::createClient();

        $difficulty1CorrectAnswer1 = '{"answer":
        [{"SceneId":"2","CharacterContainers":
        [{"containerId":"1","characterId":"2"},{"containerId":"2","characterId":"1"}]},
        {"SceneId":"2","CharacterContainers":
        [{"containerId":"1","characterId":"4"},{"containerId":"2","characterId":"3"}]},
        {"SceneId":"3","CharacterContainers":
        [{"containerId":"3","characterId":"4"}]},
        {"SceneId":"5","CharacterContainers":
        [{"containerId":"4","characterId":"3"},{"containerId":"5","characterId":"2"}]},
        {"SceneId":"1","CharacterContainers":
        [{"containerId":"6","characterId":"1"},{"containerId":"7","characterId":"3"}]},
        {"SceneId":"4","CharacterContainers":
        [{"containerId":"8","characterId":"1"},{"containerId":"9","characterId":"3"}]}]}';

        $client->request('POST', '/level/ww1/ww1-1/solution', array(), array(), array('CONTENT_TYPE' => 'application/json'), $difficulty1CorrectAnswer1);
        $this->assertResponseIsSuccessful();
        $response = $client->getResponse();
        $jsonResponse = json_decode($response->getContent(), true);
        $this->assertSame($jsonResponse['isValid'], 'true');
        $this->assertSame(implode($jsonResponse['actionIds']), '312456');

        $difficulty1CorrectAnswer2 = '{"answer":
        [{"SceneId":"2","CharacterContainers":
        [{"containerId":"1","characterId":"4"},{"containerId":"2","characterId":"3"}]},
        {"SceneId":"2","CharacterContainers":
        [{"containerId":"1","characterId":"1"},{"containerId":"2","characterId":"2"}]},
        {"SceneId":"3","CharacterContainers":
        [{"containerId":"3","characterId":"4"}]},
        {"SceneId":"5","CharacterContainers":
        [{"containerId":"4","characterId":"3"},{"containerId":"5","characterId":"2"}]},
        {"SceneId":"1","CharacterContainers":
        [{"containerId":"6","characterId":"3"},{"containerId":"7","characterId":"1"}]},
        {"SceneId":"4","CharacterContainers":
        [{"containerId":"8","characterId":"3"},{"containerId":"9","characterId":"1"}]}]}';

        $client->request('POST', '/level/ww1/ww1-1/solution', array(), array(), array('CONTENT_TYPE' => 'application/json'), $difficulty1CorrectAnswer2);
        $this->assertResponseIsSuccessful();
        $response = $client->getResponse();
        $jsonResponse = json_decode($response->getContent(), true);
        $this->assertSame($jsonResponse['isValid'], 'true');
        $this->assertSame(implode($jsonResponse['actionIds']), '132456');

        $difficulty1WrongAnswer1 = '{"answer":[]}';

        $client->request('POST', '/level/ww1/ww1-1/solution', array(), array(), array('CONTENT_TYPE' => 'application/json'), $difficulty1WrongAnswer1);
        $this->assertResponseIsSuccessful();
        $response = $client->getResponse();
        $jsonResponse = json_decode($response->getContent(), true);
        $this->assertSame($jsonResponse['isValid'], 'false');

        $difficulty1WrongAnswer2 = '{"answer":
        [{"SceneId":"2","CharacterContainers":
        [{"containerId":"1","characterId":"2"},{"containerId":"2","characterId":"1"}]},
        {"SceneId":"2","CharacterContainers":
        [{"containerId":"1","characterId":"4"},{"containerId":"2","characterId":"3"}]},
        {"SceneId":"3","CharacterContainers":
        [{"containerId":"3","characterId":"4"}]},
        {"SceneId":"5","CharacterContainers":
        [{"containerId":"4","characterId":"1"},{"containerId":"5","characterId":"2"}]},
        {"SceneId":"1","CharacterContainers":
        [{"containerId":"6","characterId":"3"},{"containerId":"7","characterId":"1"}]},
        {"SceneId":"4","CharacterContainers":
        [{"containerId":"8","characterId":"3"},{"containerId":"9","characterId":"1"}]}]}';

        $client->request('POST', '/level/ww1/ww1-1/solution', array(), array(), array('CONTENT_TYPE' => 'application/json'), $difficulty1WrongAnswer2);
        $this->assertResponseIsSuccessful();
        $response = $client->getResponse();
        $jsonResponse = json_decode($response->getContent(), true);
        $this->assertSame($jsonResponse['isValid'], 'false');
    }

    public function testHasNextDifficulty(): void{
        $client = static::createClient();

        $client->request('POST', '/level/ww1/ww1-1/has-next');
        $this->assertResponseIsSuccessful();
        $content = $client->getResponse()->getContent();
        $this->assertSame($content, 'true');

        $client->request('POST', '/level/ww1/ww1-2/has-next');
        $this->assertResponseIsSuccessful();
        $content = $client->getResponse()->getContent();
        $this->assertSame($content, 'false');
    }
}
