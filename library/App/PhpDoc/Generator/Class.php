<?php

/**
 * Класс генератор php-doc`а для указанного класса
 *
 * @author Denis Chistyakov <den.chistyakov@gmail.com>
 */
class App_PhpDoc_Generator_Class
{
    /**
     * Имя класса на основании которго генерируется док
     *
     * @var string
     */
    private $_className;

    /**
     * Объект кода генератора для класса
     *
     * @var Zend_CodeGenerator_Php_Class
     */
    private $_class;

    /**
     * Рефлексия от указанного класса
     *
     * @var ReflectionClass
     */
    private $_reflection;

    /**
     * Массив свойств, аттрибутов и констант класса
     *
     * @var array
     */
    private $_properties = array();

    /**
     * Фильтр свойств для дока
     * По-умолчанию только публичные свойства
     *
     * @var int
     */
    private $_propertiesFilter = ReflectionProperty::IS_PUBLIC;

    /**
     * Массив методов класса
     *
     * @var array
     */
    private $_methods = array();

    /**
     * Фильтр методов для дока
     * По-умолчанию только публичные методы
     *
     * @var int
     */
    private $_methodsFilter = ReflectionProperty::IS_PUBLIC;

    public function __construct($className)
    {
        $this->_className = $className;

        $this->_class = new Zend_CodeGenerator_Php_Class();
        $this->_class->setName($this->_className);

        $this->_reflection = new ReflectionClass($this->_className);
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
             ->_methods()
             ->_setProperties();

        return $this->_class->generate();
    }

    private function _docBlock()
    {
        // Данные о комментариях к классу
        if (null !== ($docBlock = $this->_getDocComment($this->_reflection))) {
            $this->_class->setDocblock($docBlock);
        }
        return $this;
    }

    private function _abstract()
    {
        $this->_class->setAbstract($this->_reflection->isAbstract());
        return $this;
    }

    private function _parent()
    {
        $parentClass = $this->_reflection->getParentClass();
        if ($parentClass instanceof ReflectionClass) {
            $this->_class->setExtendedClass($parentClass->getName());
        }
        return $this;
    }

    private function _interfaces()
    {
        $this->_class->setImplementedInterfaces($this->_reflection->getInterfaceNames());
        return $this;
    }

    private function _constants()
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

    private function _properties()
    {
        $properties = $this->_reflection->getProperties($this->_propertiesFilter);

        foreach ($properties as $property) {
            /* @var $property ReflectionProperty */

            // Определяем видимость свойства
            if ($property->isPrivate()) {
                $visibility = Zend_CodeGenerator_Php_Member_Abstract::VISIBILITY_PRIVATE;
            } else if ($property->isProtected()) {
                $visibility = Zend_CodeGenerator_Php_Member_Abstract::VISIBILITY_PROTECTED;
            } else if ($property->isPublic()) {
                $visibility = Zend_CodeGenerator_Php_Member_Abstract::VISIBILITY_PUBLIC;
            }

            // Массив с обязательными параметрами свойства
            $data = array(
                'name'         => $property->getName(),
                'visibility'   => $visibility,
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

    private function _setProperties()
    {
        $this->_class->setProperties($this->_properties);
        return $this;
    }

    private function _methods()
    {
        $methods = $this->_reflection->getMethods($this->_methodsFilter);

        foreach ($methods as $method) {
            /* @var $method ReflectionMethod */

            // Определяем видимость свойства
            if ($method->isPrivate()) {
                $visibility = Zend_CodeGenerator_Php_Member_Abstract::VISIBILITY_PRIVATE;
            } else if ($method->isProtected()) {
                $visibility = Zend_CodeGenerator_Php_Member_Abstract::VISIBILITY_PROTECTED;
            } else if ($method->isPublic()) {
                $visibility = Zend_CodeGenerator_Php_Member_Abstract::VISIBILITY_PUBLIC;
            }

            // Массив с обязательными параметрами свойства
            $data = array(
                'name'         => $method->getName(),
                'visibility'   => $visibility,
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
        $this->_class->setMethods($this->_methods);
        return $this;
    }

    /**
     * Пытаемся получить данные о комменарии из рефлексии
     *
     * @param mixed $reflection
     * @return string|null
     */
    private function _getDocComment($reflection)
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
}

