<?php
/**
 * Created by PhpStorm.
 * User: abarmin
 * Date: 17.10.16
 * Time: 20:39
 */

/**
 * Шаблон печатной формы на основе ODT-документа
 *
 * Class COdtPrintTemplate
 */
class COdtPrintTemplate implements IPrintTemplate {
    private $form;
    private $_xmlDocument;
    private $_styleXML = null;

    /**
     * @param CPrintForm $form
     */
    function __construct($form) {
        $this->form = $form;
        //
        $file = TEMPLATES_DIR.DIRECTORY_SEPARATOR.$form->template_file;
        $path = dirname($file);
        $this->_tempFileName = $path.DIRECTORY_SEPARATOR.time().'.odt';

        copy($file, $this->_tempFileName); // Copy the source File to the temp File

        $this->_objZip = new ZipArchive();
        $this->_objZip->open($this->_tempFileName);

        $this->_documentXML = $this->_objZip->getFromName('content.xml');
        $this->_styleXML = $this->_objZip->getFromName('styles.xml');
    }

    /**
     * Получить поля из шаблона
     *
     * @return IPrintClassField[]
     */
    public function getFields() {
        $fields = array();
        foreach ($this->getDocumentFields() as $name => $node) {
            $fields[] = new COdtPrintTemplateField($name, $node, false);
        }
        foreach ($this->getStyleFields() as $name => $node) {
            $fields[] = new COdtPrintTemplateField($name, $node, true);
        }
        return $fields;
    }

    /**
     * XML в виде объекта DOMDocument
     *
     * @return DOMDocument|null
     */
    private function getXMLDocument() {
        if (is_null($this->_xmlDocument)) {
            $doc = new DOMDocument();
            $doc->loadXML($this->getDocXML());
            $this->_xmlDocument = $doc;
        }
        return $this->_xmlDocument;
    }

    /**
     * Текст xml-документа, который лежит в основе
     *
     * @return mixed|null
     */
    private function getDocXML() {
        return $this->_documentXML;
    }

    /**
     * Текст xml-стиля
     *
     * @return mixed|null
     */
    private function getStyleXML() {
        return $this->_styleXML;
    }

    /**
     * Все поля из файла стилей
     *
     * @return array
     */
    private function getStyleFields() {
        $fields = array();
        $nodes = $this->getXMLStyle()->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:text:1.0", "user-field-get");
        foreach ($nodes as $node) {
            /**
             * А ведь в документе может быть несколько одинаковых
             * описателей. Складываем все в массив
             */
            $descriptors = array();
            if (array_key_exists($node->textContent, $fields)) {
                $descriptors = $fields[$node->textContent];
            }
            $descriptors[] = $node;
            $fields[$node->textContent] = $descriptors;
        }
        return $fields;
    }

    /**
     * Все поля, которые есть в документе
     *
     * @return array
     */
    private function getDocumentFields() {
        $fields = array();
        $nodes = $this->getXMLDocument()->getElementsByTagNameNS("urn:oasis:names:tc:opendocument:xmlns:text:1.0", "user-field-get");
        foreach ($nodes as $node) {
            /**
             * А ведь в документе может быть несколько одинаковых
             * описателей. Складываем все в массив
             */
            $descriptors = array();
            if (array_key_exists($node->textContent, $fields)) {
                $descriptors = $fields[$node->textContent];
            }
            $descriptors[] = $node;
            $fields[$node->textContent] = $descriptors;
        }
        return $fields;
    }


    /**
     * XML стиля в виде объекта DOMDocument
     *
     * @return DOMDocument|null
     */
    private function getXMLStyle() {
        if (is_null($this->_xmlStyle)) {
            $doc = new DOMDocument();
            $doc->loadXML($this->getStyleXML());
            $this->_xmlStyle = $doc;
        }
        return $this->_xmlStyle;
    }
}