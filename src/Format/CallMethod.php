<?php

namespace Polinome\Trieur\Format;

use Polinome\Trieur\Exception;

/**
 * @author  polinome <polinomedesign@gmail.com>
 * @license MIT http://mit-license.org/
 */
class CallMethod extends AbstractFormat
{
    /**
     * The argument's array to pass to the callable.
     */
    private array $arguments = [];

    /**
     * The method's name.
     */
    private string $method;

    /**
     * @throws \ReflectionException
     * @throws Exception
     */
    protected function init(): void
    {
        if (empty($this->cell)) {
            return;
        }

        if (!is_object($this->cell)) {
            throw new Exception(sprintf('Format class [%s] can\'t work if the cell is not an object', self::class));
        }

        if (!isset($this->conf->name)) {
            throw new Exception('Missing method\'s name');
        }

        $this->method = $this->conf->name;

        if (!method_exists($this->cell, $this->method)) {
            throw new Exception(sprintf('Method [%s] does not exist in class [%s]', $this->method, $this->cell::class));
        }

        $parameters = $this->getMethodParameters();

        $argumentsByName = [];
        if (isset($this->conf->arguments)) {
            $argumentsByName = $this->conf->arguments;
        }

        $this->arguments = [];
        foreach ($parameters as $parameter) {
            if (!isset($argumentsByName[$parameter->name])) {
                if ($parameter->isOptional()) {
                    break;
                }

                throw new Exception(sprintf('Missing argument [%s] for method [%s]', $parameter->name, $this->method));
            }

            $this->arguments[] = $argumentsByName[$parameter->name];
        }
    }

    public function render()
    {
        if (empty($this->cell)) {
            return $this->cell;
        }

        return call_user_func_array([$this->cell, $this->method], $this->arguments);
    }

    /**
     * Get a class method arguments' name list.
     *
     * @return \ReflectionParameter[]
     *
     * @throws \ReflectionException
     */
    private function getMethodParameters(): array
    {
        $c = new \ReflectionClass($this->cell);
        $m = $c->getMethod($this->method);

        $parameters = [];
        foreach ($m->getParameters() as $param) {
            $parameters[] = $param;
        }

        return $parameters;
    }
}
