<?php 

namespace Versatile\Support\FormObject;

use FormObject\Form;
use FormObject\Field;
use FormObject\Field\Action;
use FormObject\Naming\NamerInterface;
use Versatile\Introspection\Contracts\TitleIntrospector;


class TitleIntrospectorNamer implements NamerInterface
{

    protected $lang;

    protected $formKeyCache = [];

    protected $cache = [];

    protected $titles;

    public function __construct(TitleIntrospector $titles)
    {
        $this->titles = $titles;
    }

    /**
     * {@inheritdoc}
     *
     * @param \FormObject\Form $form
     * @param \FormObject\Field $field
     * @return string|null
     **/
    public function getTitle(Form $form, Field $field)
    {
        return $this->translateProperty($form, $field, 'title');
    }

    /**
     * {@inheritdoc}
     *
     * @param \FormObject\Form $form
     * @param \FormObject\Field $field
     * @return string|null
     **/
    public function getDescription(Form $form, Field $field)
    {
        return $this->translateProperty($form, $field, 'description');
    }

    /**
     * {@inheritdoc}
     *
     * @param \FormObject\Form $form
     * @param \FormObject\Field $field
     * @return string|null
     **/
    public function getTooltip(Form $form, Field $field)
    {
        return $this->translateProperty($form, $field, 'tooltip');
    }

    protected function translateProperty(Form $form, Field $field, $property)
    {

        if ($property != 'title') {
            return;
        }

        $model = $form->getModel();

        if (!$model) {
            $model = $this->modelOfFormClass($form);
        }

        return $this->titles->keyTitle($model, $this->fieldKey($field));

    }

    protected function fieldKey(Field $field)
    {
        return str_replace('__', '.', $field->getName());
    }

    protected function modelOfFormClass($form)
    {
        $formClass = get_class($form);

        if (substr($formClass, -4) == 'Form') {
            return substr($formClass, 0, strlen($formClass)-4);
        }
    }

    protected function baseName($class)
    {
        $matches = [];

        if (preg_match('@\\\\([\w]+)$@', $modelName, $matches)) {
            return $matches[1];
        }

    }

}