<?php 

use Mockery as m;

use Illuminate\Database\Eloquent\Model;
use Versatile\Attributes\BitMaskAttribute;

use Versatile\Attributes\UsesVirtualAttributes;
use Versatile\Attributes\Dispatcher;

class BitMaskAttributeTest extends BaseTest
{

    public function testImplementsInterface()
    {
        $this->assertInstanceOf(
            'Versatile\Attributes\Contracts\Provider',
            $this->newBitMaskAttribute()
        );
    }

    public function testQueriesAttributeFromModel()
    {
        $model = $this->mockModel();
        $attributesModel = $this->newVirtualAttributesModel();
        $key = 'show_in_menu';
        $bitKey = 'visibility';
        $bitIndex = '1';
        $attributesModel->virtualAttributes[$key] = "$bitKey,$bitIndex";

        $targetKey = 'visibility';
        $provider = $this->newBitMaskAttribute($bitKey, $bitIndex);

        $model->shouldReceive('getAttribute')
              ->with($bitKey)
              ->once()
              ->andReturn(0);

        $this->assertFalse($provider->getAttribute($model, $key));

        $model->shouldReceive('getAttribute')
              ->with($bitKey)
              ->once()
              ->andReturn(1);

        $this->assertTrue($provider->getAttribute($model, $key));

        $model->shouldReceive('getAttribute')
              ->with($bitKey)
              ->once()
              ->andReturn(2);

        $this->assertFalse($provider->getAttribute($model, $key));

        $model->shouldReceive('getAttribute')
              ->with($bitKey)
              ->once()
              ->andReturn(5);

        $this->assertTrue($provider->getAttribute($model, $key));
    }

    public function testSetsAttribute()
    {
        $model = $this->mockModel();
        $attributesModel = $this->newVirtualAttributesModel();
        $key = 'show_in_menu';
        $bitKey = 'visibility';
        $bitIndex = '1';
        $attributesModel->virtualAttributes[$key] = "$bitKey,$bitIndex";

        $provider = $this->newBitMaskAttribute($bitKey, $bitIndex);

        $model->shouldReceive('getAttribute')
              ->with($bitKey)
              ->once()
              ->andReturn(2);

        $this->assertFalse($provider->getAttribute($model, $key));

        $model->shouldReceive('getAttribute')
              ->with($bitKey)
              ->once()
              ->andReturn(2);


        $model->shouldReceive('setAttribute')
              ->with($bitKey, 3)
              ->once();


        $provider->setAttribute($model, $key, true);

        $model->shouldReceive('getAttribute')
              ->with($bitKey)
              ->once()
              ->andReturn(3);

        $this->assertTrue($provider->getAttribute($model, $key));

    }

    public function testGetAttributeBitCalculation()
    {
        $model = new PageModel;

        $model->virtualAttributes = [
            'show_in_menu'       => 'bitmask:visibility,1',
            'show_in_aside_menu' => 'bitmask:visibility,2',
            'show_in_search'     => 'bitmask:visibility,4',
            'show_in_api'        => 'bitmask:visibility,8'
        ];

        Dispatcher::extend('bitmask', function($bitKey, $bitName){
            return new BitMaskAttribute($bitKey, $bitName);
        });

        $model->visibility = 0;

        $this->assertFalse($model->show_in_menu);
        $this->assertFalse($model->show_in_aside_menu);
        $this->assertFalse($model->show_in_search);
        $this->assertFalse($model->show_in_api);

        $model->show_in_menu = true;

        $this->assertTrue($model->show_in_menu);
        $this->assertFalse($model->show_in_aside_menu);
        $this->assertFalse($model->show_in_search);
        $this->assertFalse($model->show_in_api);
        $this->assertEquals(1, $model->visibility);

        $model->show_in_aside_menu = true;

        $this->assertTrue($model->show_in_menu);
        $this->assertTrue($model->show_in_aside_menu);
        $this->assertFalse($model->show_in_search);
        $this->assertFalse($model->show_in_api);
        $this->assertEquals(3, $model->visibility);

        $model->show_in_api = true;

        $this->assertTrue($model->show_in_menu);
        $this->assertTrue($model->show_in_aside_menu);
        $this->assertFalse($model->show_in_search);
        $this->assertTrue($model->show_in_api);
        $this->assertEquals(11, $model->visibility);

        $model->show_in_api = false;

        $this->assertTrue($model->show_in_menu);
        $this->assertTrue($model->show_in_aside_menu);
        $this->assertFalse($model->show_in_search);
        $this->assertFalse($model->show_in_api);
        $this->assertEquals(3, $model->visibility);

        $model->show_in_menu = false;

        $this->assertFalse($model->show_in_menu);
        $this->assertTrue($model->show_in_aside_menu);
        $this->assertFalse($model->show_in_search);
        $this->assertFalse($model->show_in_api);
        $this->assertEquals(2, $model->visibility);

    }

    protected function newBitMaskAttribute($bitKey='visibility', $bitName='1')
    {
        return new BitMaskAttribute($bitKey, $bitName);
    }


}

class PageModel extends Model
{
    use UsesVirtualAttributes;

    public $virtualAttributes = [
        'show_in_menu' => 'bitmask:visibility,1'
    ];
}

