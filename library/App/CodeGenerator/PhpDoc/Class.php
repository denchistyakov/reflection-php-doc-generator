<?php

/**
 * Класс генератор php-doc`а для указанного класса
 *
 * @author Denis Chistyakov <den.chistyakov@gmail.com>
 */
class App_CodeGenerator_PhpDoc_Class extends Zend_CodeGenerator_Php_Class
{

    /**
     * Рефлексия от указанного класса
     *
     * @var ReflectionClass
     */
    protected $_reflection;

    /**
     * Массив свойств, аттрибутов и констант класса
     *
     * @var array
    protected $_properties = array();
     */

    /**
     * Фильтр свойств для дока
     * По-умолчанию только публичные свойства
     *
     * @var int
     */
    protected $_propertiesFilter = ReflectionProperty::IS_PUBLIC;

    /**
     * Массив методов класса
     *
     * @var array
    protected $_methods = array();
     */

    /**
     * Фильтр методов для дока
     * По-умолчанию только публичные методы
     *
     * @var int
     */
    protected $_methodsFilter = ReflectionProperty::IS_PUBLIC;

    public function __construct($className)
    {
        $this->setName($className);
        $this->_reflection = new ReflectionClass($className);
    }

    /**
     * Устанавливаем фильтр для свойств
     *
     * @param int $filter
     * @return App_PhpDoc_Generator_Class
     */
    public function setPropertiesFilter($filter)
    {
        $this->_propertiesFilter = (int) $filter;
        return $this;
    }

    /**
     * Устанавливаем фильтр для методов
     *
     * @param int $filter
     * @return App_PhpDoc_Generator_Class
     */
    public function setMethodsFilter($filter)
    {
        $this->_methodsFilter = (int) $filter;
        return $this;
    }

    /**
     * Генерируем код php-doc`а
     *
     * @return string
     */
    public function generate()
    {
        $this->_docBlock()
             ->_abstract()
             ->_parent()
             ->_interfaces()
             ->_constants()
             ->_properties()
             ->_setProperties()
             ->_methods()
             ;
//        return parent::generate();
    }

    protected function _docBlock()
    {
        // Данные о комментариях к классу
        if (null !== ($docBlock = $this->_getDocComment($this->_reflection))) {
            $this->setDocblock($docBlock);
        }
        return $this;
    }

    protected function _abstract()
    {
        $this->setAbstract($this->_reflection->isAbstract());
        return $this;
    }

    protected function _parent()
    {
        $parentClass = $this->_reflection->getParentClass();
        if ($parentClass instanceof ReflectionClass) {
            $this->setExtendedClass($parentClass->getName());
        }
        return $this;
    }

    protected function _interfaces()
    {
        $this->setImplementedInterfaces($this->_reflection->getInterfaceNames());
        return $this;
    }

    protected function _constants()
    {
        $consts = $this->_reflection->getConstants();
        foreach ($consts as $name => $defaultValue) {
            $this->_properties[] = array(
                'name'         => $name,
                'const'        => true,
                'defaultValue' => $defaultValue,
            );
        }
        return $this;
    }

    protected function _properties()
    {
        $properties = $this->_reflection->getProperties($this->_propertiesFilter);

        foreach ($properties as $property) {
            /* @var $property ReflectionProperty */

            // Массив с обязательными параметрами свойства
            $data = array(
                'name'         => $property->getName(),
                'visibility'   => $this->_getVisibility($property),
                'static'       => $property->isStatic(),
            );

            // Пытаемся получить значение по-умолчанию
            if ($property->isDefault()) {
                $property->setAccessible(true);
                $data['defaultValue'] = $property->getValue($this->_reflection);
            }

            // Данные о комментариях к свойству
            if (null !== ($docBlock = $this->_getDocComment($property))) {
                $data['docBlock'] = $docBlock;
            }
            $this->_properties[] = $data;
        }
        return $this;
    }

    protected function _setProperties()
    {
        $this->setProperties($this->_properties);
        return $this;
    }

    protected function _methods()
    {
        $methods = $this->_reflection->getMethods($this->_methodsFilter);

        foreach ($methods as $method) {
            /* @var $method ReflectionMethod */

            // Массив с обязательными параметрами свойства
            $data = array(
                'name'         => $method->getName(),
                'visibility'   => $this->_getVisibility($method),
                'static'       => $method->isStatic(),
                'abstract'     => $method->isAbstract(),
                'closure'      => $method->isClosure(),
                'final'        => $method->isFinal(),
            );

            // Данные о комментариях к методу
            if (null !== ($docBlock = $this->_getDocComment($method))) {
                $data['docBlock'] = $docBlock;
            }

            // Данные о параметрах матода
            $parameters = $method->getParameters();
            $parametersData = array();
            foreach ($parameters as $parameter) {
                /* @var $parameter ReflectionParameter */

                $parameterData = array(
                    'name' => $parameter->getName(),
                );

                if ($parameter->isDefaultValueAvailable()) {
                    $parameterData['defaultValue'] = $parameter->getDefaultValue();
                }

                $parametersData[] = $parameterData;
            }
            $data['parameters'] = $parametersData;

            $this->_methods[] = $data;
        }
        $this->setMethods($this->_methods);
        return $this;
    }

    /**
     * Пытаемся получить данные о комменарии из рефлексии
     *
     * @param mixed $reflection
     * @return string|null
     */
    protected function _getDocComment($reflection)
    {
        $docComment = $reflection->getDocComment();
        if (!empty($docComment)) {
            try {
                $docComment = new Zend_Reflection_Docblock($docComment);
            } catch (Zend_Reflection_Exception $e) {
                return null;
            }
            return $docComment->getContents();
        }
        return null;
    }

    /**
     * Определяем видимость рефлексии метода или свойства
     *
     * @param mixed $reflection
     * @return string
     */
    protected function _getVisibility($reflection)
    {
        if ($reflection->isPrivate()) {
            $visibility = Zend_CodeGenerator_Php_Member_Abstract::VISIBILITY_PRIVATE;
        } else if ($reflection->isProtected()) {
            $visibility = Zend_CodeGenerator_Php_Member_Abstract::VISIBILITY_PROTECTED;
        } else if ($reflection->isPublic()) {
            $visibility = Zend_CodeGenerator_Php_Member_Abstract::VISIBILITY_PUBLIC;
        }
        return $visibility;
    }

}
