<?php namespace Versatile\Attributes;

use Illuminate\Database\Eloquent\Model;
use Versatile\Attributes\Contracts\Provider;

class BitMaskAttribute implements Provider
{

    protected $bitKey;

    protected $bitName = 1;

    public function __construct($bitKey, $bitName)
    {
        $this->bitKey = $bitKey;
        $this->bitName = (int)$bitName;
    }

    public function getAttribute(Model $model, $key)
    {
        $bitValue = (int)$model->{$this->bitKey};
        return (bool)($bitValue & $this->bitName);
    }

    public function setAttribute(Model $model, $key, $value)
    {

        $oldBitValue = (int)$model->{$this->bitKey};
        $bitIndex = strlen(decbin($this->bitName))-1;

        if ($value) {
            $newBitValue = $oldBitValue|(1<<$bitIndex);
        } else {
            $oldBitString = decbin($oldBitValue);
            $oldBitString[strlen($oldBitString)-1-$bitIndex] = '0';
            $newBitValue = bindec($oldBitString);
        }

        $model->{$this->bitKey} = $newBitValue;

    }

}