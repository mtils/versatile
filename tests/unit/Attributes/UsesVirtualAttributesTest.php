<?php 

use Mockery as m;

use Illuminate\Database\Eloquent\Model;
use Versatile\Attributes\UsesVirtualAttributes;
use Versatile\Attributes\Dispatcher;

class UsesVirtualAttributesTest extends BaseTest
{

    public function testHasAttributeReturnsTrueIfSetted()
    {
        $model = $this->newVirtualAttributesModel();
        $this->assertTrue($model->hasVirtualAttribute('show_in_menu'));
        $this->assertEquals($model->virtualAttributes, $model->getVirtualAttributes());
    }

    public function testHasAttributeReturnsFalseIfSetted()
    {
        $model = $this->newVirtualAttributesModel();
        $this->assertFalse($model->hasVirtualAttribute('foo'));
    }

    public function testHasAttributeWithoutPropertyReturnsFalse()
    {
        $model = new VirtualAttributesWithoutPropertyModel();
        $this->assertFalse($model->hasVirtualAttribute('foo'));
    }

    public function testGetDispatcherReturnsDispatcherIfNoneSet()
    {
        $model = $this->newVirtualAttributesModel();

        $dispatcher1 = VirtualAttributesModel::getVirtualAttributeDispatcher();
        $dispatcher2 =  VirtualAttributesWithoutPropertyModel::getVirtualAttributeDispatcher();

        $this->assertInstanceOf(
            'Versatile\Attributes\Dispatcher',
            $dispatcher1
        );

        $this->assertInstanceOf(
            'Versatile\Attributes\Dispatcher',
            $dispatcher2
        );

        $this->assertNotSame($dispatcher1, $dispatcher2);

        $this->assertSame(
            $dispatcher1,
            VirtualAttributesModel::getVirtualAttributeDispatcher()
        );
    }

    public function testGetVirtualAttributeValueForwardsToDispatcher()
    {

        $model = $this->newVirtualAttributesModel();
        $dispatcher = $this->mockAttributesDispatcher();

        VirtualAttributesModel::setVirtualAttributeDispatcher($dispatcher);
        $model = new VirtualAttributesModel;
        $key = 'show_in_menu';

        $this->assertSame(
            $dispatcher,
            VirtualAttributesModel::getVirtualAttributeDispatcher()
        );


        $dispatcher->shouldReceive('getAttribute')
                   ->with($model, $key)
                   ->andReturn('test');

        $this->assertEquals('test', $model->show_in_menu);

    }

    public function testSetVirtualAttributeValueForwardsToDispatcher()
    {

        $model = $this->newVirtualAttributesModel();
        $dispatcher = $this->mockAttributesDispatcher();

        VirtualAttributesModel::setVirtualAttributeDispatcher($dispatcher);
        $model = new VirtualAttributesModel;
        $key = 'show_in_menu';
        $value = true;

        $dispatcher->shouldReceive('setAttribute')
                   ->with($model, $key, $value)
                   ->once()
                   ->andReturn('test');

        $model->show_in_menu = $value;

    }

    public function testSetVirtualAttributeValueSkipsDispatcherIfNotSet()
    {

        $model = $this->newVirtualAttributesModel();
        $dispatcher = $this->mockAttributesDispatcher();

        VirtualAttributesModel::setVirtualAttributeDispatcher($dispatcher);
        $model = new VirtualAttributesModel;
        $key = 'foo';
        $value = true;

        $dispatcher->shouldReceive('setAttribute')
                   ->with($model, $key, $value)
                   ->never();

        $model->foo = $value;

    }


}
class VirtualAttributesWithoutPropertyModel extends Model
{
    use UsesVirtualAttributes;
}