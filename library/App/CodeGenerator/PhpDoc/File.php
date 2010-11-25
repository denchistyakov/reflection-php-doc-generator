<?php

/**
 * Класс генератор php-скрипта с php-doc`ами для указанных классов
 *
 * @author Denis Chistyakov <den.chistyakov@gmail.com>
 */
class App_CodeGenerator_PhpDoc_File extends Zend_CodeGenerator_Php_File
{

    /**
     * Массив сгенерированных классов
     *
     * @var array
     */
    protected $_classes = array();

    /**
     * Список классов для генерации
     *
     * @var array
     */
    protected $_classNames = array();

    /**
     * Имя файла для дока
     *
     * @var string
     */
    protected $_filename;

    public function __construct(array $options = array())
    {
        if (isset($options['classes'])) {
            $this->setClassNames($options['classes']);
        }

        if (isset($options['filename'])) {
            $this->setFilename($options['filename']);
        }
    }

    /**
     * Устанавливаем список классов для генерации
     *
     * @param array $classes
     * @return App_CodeGenerator_PhpDoc_File
     */
    public function setClassNames($classNames)
    {
        $this->_classNames = (array) $classNames;
        return $this;
    }

    /**
     * Получаем список классов
     *
     * @return array
     */
    public function getClassNames()
    {
        return $this->_classNames;
    }

    /**
     * Генерируем код php-скрипта
     *
     * @throws App_CodeGenerator_PhpDoc_Exception
     * @return string
     */
    public function generate()
    {
        if (empty($this->_classNames)) {
            throw new App_CodeGenerator_PhpDoc_Exception('Classes list is empty');
        }

        $classes = array();
        foreach ($this->_classNames as $class) {
            $classes[] = new App_CodeGenerator_PhpDoc_Class($class);
        }

        $this->setClasses($classes);
        return parent::generate();
    }

    /**
     * Сохраняем сгенерированный код в файл
     *
     * @throws App_CodeGenerator_PhpDoc_Exception
     * @return App_CodeGenerator_PhpDoc_File
    public function write()
    {
        $this->generate();

        if (empty($this->_filename)) {
            throw new App_CodeGenerator_PhpDoc_Exception('Filename is empty');
        }

        $this->_file->setFilename($this->_filename)
                    ->write();
        return $this;
    }
     */

}
