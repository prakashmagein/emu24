<?php
namespace Swissup\Amp\Model\Html\Filter\Dom;

class Form extends DomAbstract
{
    /**
     * Before adding some action, follow the list below:
     *
     *  1. Request MUST return AMP-Access-Control-Allow-Source-Origin header
     *  2. Request MUST return json data
     *  3. Form MUST implement "on" attribute to control returned json
     *
     * @return array
     */
    protected function getSupportedActions()
    {
        return [
            'checkout/cart/add',
            'catalog/product_compare/add',
            'catalog/product_compare/remove',
            'wishlist/index/add',
            'catalogsearch',
            'search',
            'contact/index/post'
        ];
    }

    /**
     * Check if the form node can be rendered
     *
     * @param  \DomElement $node
     * @return boolean
     */
    protected function canUse($node)
    {
        foreach ($this->getSupportedActions() as $action) {
            if (false !== strpos($node->getAttribute('action'), $action)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the node is add to cart form
     *
     * @param  \DomElement $node
     * @return boolean
     */
    protected function isAddToCart($node)
    {
        if (false !== strpos($node->getAttribute('action'), 'checkout/cart/add')) {
            return true;
        }

        return false;
    }

    /**
     * Prepare form attributes to match amp requirements
     *
     * @param  \DOMDocument $document
     * @return void
     */
    public function process($document)
    {
        $remove = [];
        $nodes = $document->getElementsByTagName('form');
        $nodesCount = (int)$nodes->length;
        $xpath = new \DOMXPath($document);
        foreach ($nodes as $node) {
            if ($node->hasAttribute('action-xhr')) {
                continue;
            }

            if (!$this->canUse($node)) {
                $remove[] = $node;
                continue;
            }

            $submit = $xpath->query('.//*[@type="submit"]', $node);
            if (!$submit->length) {
                // if form has one button only - transform it to submit
                $button = $xpath->query('.//button[@type="button" and not(@on)]', $node)->item(0);
                if ($button) {
                    $button->setAttribute('type', 'submit');
                } else {
                    // Out of stock items does not have submit button
                }
            } else if ($submit->item(0)->hasAttribute('disabled')) {
                $submit->item(0)->removeAttribute('disabled');
            }

            // update minicart on form submit-success
            if ($this->isAddToCart($node)) {
                $node->setAttribute('on', 'submit-success:minicart.refresh');
            }

            $this->prepareActionAttribute($node);

            if (!$node->hasAttribute('target')) {
                $node->setAttribute('target', '_top');
            }

            // add success/error handling
            $method = strtolower($node->getAttribute('method'));
            if ('post' === $method) {
                $this->prepareResponseRendering($node, $document);

                // fix to allow to use form from google cache for new visitors
                $hiddenInput = $document->createElement('input');
                $hiddenInput->setAttribute('type', 'hidden');
                $hiddenInput->setAttribute('name', 'nocookie');
                $hiddenInput->setAttribute('value', '1');
                $node->appendChild($hiddenInput);
            } else {
                // provide stateful AMP browsing
                $hiddenInput = $document->createElement('input');
                $hiddenInput->setAttribute('type', 'hidden');
                $hiddenInput->setAttribute('name', 'amp');
                $hiddenInput->setAttribute('value', 1);
                $node->appendChild($hiddenInput);
            }

            // fix cached form_key
            $formKey = $xpath->query('.//input[@name="form_key"]', $node)->item(0);
            if ($formKey) {
                $formKey->setAttribute('value', $this->formKey->getFormKey());
            }
        }

        foreach ($remove as $node) {
            $node->parentNode->removeChild($node);
        }

        if ($nodesCount > count($remove)) {
            $this->addAmpComponent(
                'amp-form',
                'https://cdn.ampproject.org/v0/amp-form-0.1.js'
            );
        }
    }

    /**
     * 1. Remove http protocol (https is allowed only)
     * 2. Replace action with xhr-action if needed
     * 3. Add amp parameter to the query
     *
     * @param  \DOMElement $node
     * @return void
     */
    protected function prepareActionAttribute($node)
    {
        $actionAttribute = 'action';
        $action = $node->getAttribute($actionAttribute);
        $action = str_replace('http://', '//', $action);
        $method = strtolower($node->getAttribute('method'));

        // provide stateful AMP browsing
        if ('post' === $method && false === strpos($action, 'amp=1')) {
            if (false === strpos($action, '?')) {
                $action .= '?amp=1';
            } else {
                $action .= '&amp=1';
            }
        }

        if ('post' === $method) {
            $node->removeAttribute($actionAttribute);
            $actionAttribute = 'action-xhr';
        }
        $node->setAttribute($actionAttribute, $action);
    }

    /**
     * Replace action with xhr-action. Add amp parameter to the query
     *
     * @param  \DOMElement $node
     * @return void
     */
    protected function prepareResponseRendering($node, $document)
    {
        $xpath = new \DOMXPath($document);

        $submitSuccess = $xpath->query('.//div[@submit-success]', $node);
        if (!$submitSuccess->length) {
            $wrapper = $document->createElement('div');
            $wrapper->setAttribute('class', 'form-response-message success');
            $wrapper->appendChild($document->createTextNode($this->getSuccessTemplate()));

            $template = $document->createElement('template');
            $template->setAttribute('type', 'amp-mustache');
            $template->appendChild($wrapper);

            $submitSuccess = $document->createElement('div');
            $submitSuccess->setAttribute('submit-success', '');
            $submitSuccess->setAttribute('class', 'form-response');
            $submitSuccess->appendChild($template);

            // insert it next to submit button
            $submit = $xpath->query('.//*[@type="submit"]', $node)->item(0);
            if ($submit && $submit->parentNode->tagName === 'form') {
                $submit->parentNode->insertBefore($submitSuccess, $submit->nextSibling);
            } else {
                $node->appendChild($submitSuccess);
            }
        }

        $submitError = $xpath->query('.//div[@submit-error]', $node);
        if (!$submitError->length) {
            $wrapper = $document->createElement('div');
            $wrapper->setAttribute('class', 'form-response-message error');
            $wrapper->appendChild($document->createTextNode($this->getErrorTemplate()));

            $template = $document->createElement('template');
            $template->setAttribute('type', 'amp-mustache');
            $template->appendChild($wrapper);

            $submitError = $document->createElement('div');
            $submitError->setAttribute('submit-error', '');
            $submitError->setAttribute('class', 'form-response');
            $submitError->appendChild($template);

            // insert it next to submit button
            $submit = $xpath->query('.//*[@type="submit"]', $node)->item(0);
            if ($submit && $submit->parentNode->tagName === 'form') {
                $submit->parentNode->insertBefore($submitError, $submit->nextSibling);
            } else {
                $node->appendChild($submitError);
            }
        }
    }

    /**
     * Returns mustache template for success messages
     *
     * @return string
     */
    protected function getSuccessTemplate()
    {
        return "{{#messages.success}}\n{{{.}}}\n{{/messages.success}}\n\n";
    }

    /**
     * Returns mustache template for error messages
     *
     * @return string
     */
    protected function getErrorTemplate()
    {
        return "{{#messages.error}}\n{{{.}}}\n{{/messages.error}}\n\n";
    }
}
