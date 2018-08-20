<?php
namespace Kruzya\TelegramNotifications;

class HtmlPurifier {
  /**
   * Static entry point (factory).
   */
  public static function purify($text, array $allowedTags = []) {
    $purifier = new self($text);
    return $purifier->stripTags(Utils::getArrayWithKeys($allowedTags))
      ->stripAttributes($allowedTags)
      ->render();
  }

  /**
   * All internal stuff.
   */
  private $text;
  private function __construct($text) {
    $this->text = $text;
  }

  public function stripTags(array $allowedTags = []) {
    $tags = '';
    foreach ($allowedTags as $tag)
      $tags .= "<{$tag}>";

    $this->text = strip_tags($this->text, $tags);
    return $this;
  }

  public function stripAttributes(array $allowedAttributes = []) {
    $dom = new \DOMDocument('1.0', 'utf-8');
    if ($dom->loadHTML('<?xml encoding="UTF-8">' . $this->text)) {
      foreach ($dom->childNodes as $item) {
        if ($item->nodeType == XML_PI_NODE) {
          $dom->removeChild($item);
        }
      }
    }

    $body = $dom->getElementsByTagName('body');
    $body = $body->item(0);
    $this->walkNode($allowedAttributes, $body);

    $NewDocument = new \DOMDocument();
    $NewDocument->appendChild($NewDocument->importNode($body->ownerDocument->documentElement->firstChild, true));

    $this->text = trim(str_replace(['<body>', '</body>'], '', $NewDocument->saveHTML()));

    return $this;
  }

  public function render() {
    return trim($this->text);
  }

  /**
   * Walkers
   */
  private function walkNode(array $allowedAttributes, \DOMNode $node) {
    // Clear attributes (if required).
    if (isset($allowedAttributes[$node->nodeName]))
      $this->walkAttributes($node, $allowedAttributes[$node->nodeName]);

    // Clear childrens.
    $this->walkChildrens($allowedAttributes, $node);
  }

  private function walkAttributes(\DOMNode $node, array $attributeKeys) {
    if (!$node->hasAttributes()) {
      return;
    }

    $attributesToDelete = [];

    // Loop all exists attributes.
    foreach ($node->attributes as $attribute) {
      $name = $attribute->name;
      if (!Utils::itemExists($attributeKeys, $name)) {
        $this->_log('deleting attribute #1 ' . $name);
        $attributesToDelete[] = $name;
      }
    }

    // Delete all required attributes.
    foreach ($attributesToDelete as $attribute) {
      $this->_log('deleting attribute #2 ' . $attribute);
      $node->removeAttribute($attribute);
    }
  }

  private function walkChildrens(array $allowedTags, \DOMNode $node) {
    if (!$node->hasChildNodes()) {
      return;
    }

    foreach ($node->childNodes as $childNode) {
      $this->walkNode($allowedTags, $childNode);
    }
  }
}