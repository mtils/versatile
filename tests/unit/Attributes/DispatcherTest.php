<?php

use Mockery as m;

use Versatile\Attributes\Dispatcher;

class DispatcherTest extends BaseTest
{

    public function testSplitConfigurationSplitsIntoNameIfNoParamsSet()
    {

        $dispatcher = $this->newDispatcher();
        $config = 'bitmask';

        $shouldBe = ['bitmask',[]];

        $this->assertEquals($shouldBe, $dispatcher->splitConfiguration($config));
    }

    public function testSplitConfigurationSplitsIntoNameAndParamsWhenOneParam()
    {

        $dispatcher = $this->newDispatcher();
        $config = 'bitmask:visibility,1';

        $shouldBe = ['bitmask',['visibility','1']];

        $this->assertEquals($shouldBe, $dispatcher->splitConfiguration($config));
    }

    public function testExtendAddsFactoryAndUsesIt()
    {

        $dispatcher = $this->newDispatcher();
        $config = 'bitmask:visibility,1';

        $shouldBe = ['bitmask',['visibility','1']];

        $this->assertEquals($shouldBe, $dispatcher->splitConfiguration($config));
    }

    public function testGetProviderCallsFactoryWithParams()
    {
        $dispatcher = $this->newDispatcher();
        $model = $this->newVirtualAttributesModel();
        $key = 'show_in_menu';

        $returnValue = [
            'targetKey' => 'visibility',
            'bit'       => '1',
            'test'      => true
        ];

        $dispatcher->extend('bitmask', function($targetKey, $bit){
            return [
                'targetKey' => $targetKey,
                'bit'       => $bit,
                'test'      => true
            ];
        });

        $this->assertEquals($returnValue, $dispatcher->getProvider($model, $key));

    }

    public function testCreateProviderThrowsExceptionIfVirtualKeyNotSet()
    {
        $this->expectException(\OutOfBoundsException::class);
        $dispatcher = $this->newDispatcher();
        $provider = m::mock('Versatile\Attributes\Contracts\Provider');
        $model = $this->newVirtualAttributesModel();
        $key = 'foo';

        $dispatcher->createProvider($model, $key);

    }

    public function testCreateProviderThrowsExceptionIfFactoryNotSet()
    {
        $this->expectException(\OutOfBoundsException::class);
        $dispatcher = $this->newDispatcher();
        $provider = m::mock('Versatile\Attributes\Contracts\Provider');
        $model = $this->newVirtualAttributesModel();
        $model->virtualAttributes['blub'] = 'blib';
        $key = 'blub';

        $dispatcher->createProvider($model, $key);

    }

    public function testSetProviderSetsProvider()
    {
        $dispatcher = $this->newDispatcher();
        $provider = m::mock('Versatile\Attributes\Contracts\Provider');
        $model = $this->newVirtualAttributesModel();
        $key = 'foo';

        $dispatcher->setProvider($key, $provider);

        $this->assertSame($provider, $dispatcher->getProvider($model, $key));

    }

    public function testGetAttributeForwardsToProvider()
    {
        $dispatcher = $this->newDispatcher();
        $provider = m::mock('Versatile\Attributes\Contracts\Provider');
        $model = $this->newVirtualAttributesModel();
        $key = 'foo';

        $dispatcher->setProvider($key, $provider);

        $provider->shouldReceive('getAttribute')
                 ->with($model, $key)
                 ->andReturn('blub');

        $this->assertEquals('blub', $dispatcher->getAttribute($model, $key));

    }

    public function testSetAttributeForwardsToProvider()
    {
        $dispatcher = $this->newDispatcher();
        $provider = m::mock('Versatile\Attributes\Contracts\Provider');
        $model = $this->newVirtualAttributesModel();
        $key = 'foo';
        $value = 'Hansi';

        $dispatcher->setProvider($key, $provider);

        $provider->shouldReceive('setAttribute')
                 ->with($model, $key, $value)
                 ->andReturn('blub');

        $this->assertEquals('blub', $dispatcher->setAttribute($model, $key, $value));

    }

    protected function newDispatcher()
    {
        return new Dispatcher();
    }

}