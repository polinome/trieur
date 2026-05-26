<?php

namespace Polinome\Trieur\Format;

use Polinome\Trieur\Exception;

/**
 * @author  polinome <polinomedesign@gmail.com>
 * @license MIT http://mit-license.org/
 */
class Callback extends AbstractFormat
{
    /**
     * The argument's array to pass to the callable.
     */
    private array $arguments = [];

    /**
     * @throws \ReflectionException
     * @throws Exception
     */
    protected function init(): void
    {
        if (!isset($this->$config['name'])) {
            throw new Exception('Missing output callback\'s name');
        }

        $callableName = $this->config['name'];
        if (!is_string($this->config['name'])) {
            $this->$config['name'] = array_values((array) $this->config['name']);
            $callableName = '(array) '.implode('::', $this->config['name']);
        }

        if (!is_callable($this->config['name'])) {
            throw new Exception(sprintf('Callback [%s] does not exist', $callableName));
        }

        $parameters = $this->getParameters();

        $argumentsByName = [];
        if (isset($this->conf->arguments)) {
            $argumentsByName = $this->conf->arguments;
        }

        if (isset($this->conf->cell)) {
            $argumentsByName[$this->conf->cell] = $this->cell;
        }

        if (isset($this->conf->row)) {
            $argumentsByName[$this->conf->row] = $this->row;
        }

        $this->arguments = [];
        foreach ($parameters as $parameter) {
            if (!isset($argumentsByName[$parameter->name])) {
                if ($parameter->isOptional()) {
                    break;
                }

                throw new Exception(sprintf('Missing argument [%s] for callback [%s]', $parameter->name, $callableName));
            }

            $this->arguments[] = $argumentsByName[$parameter->name];
        }
    }

    public function render()
    {
        return call_user_func_array($this->config['name'], $this->arguments);
    }

    /**
     * Get a callable arguments' name list.
     *
     * @return \ReflectionParameter[]
     *
     * @throws \ReflectionException
     */
    private function getParameters(): array
    {
        if (is_string($this->config['name'])) {
            return $this->getFunctionParameters($this->config['name']);
        }

        return $this->getMethodParameters((array) $this->config['name']);
    }

    /**
     * Get a function arguments' name list.
     *
     * @param string $functionName The function name
     *
     * @return \ReflectionParameter[]
     *
     * @throws \ReflectionException
     */
    private function getFunctionParameters(string $functionName): array
    {
        $f = new \ReflectionFunction($functionName);

        $parameters = [];
        foreach ($f->getParameters() as $param) {
            $parameters[] = $param;
        }

        return $parameters;
    }

    /**
     * Get a class method arguments' name list.
     *
     * @param array $callable A callable array
     *
     * @return \ReflectionParameter[]
     *
     * @throws \ReflectionException
     */
    private function getMethodParameters(array $callable): array
    {
        [$className, $method] = $callable;

        $c = new \ReflectionClass($className);
        $m = $c->getMethod($method);

        $parameters = [];
        foreach ($m->getParameters() as $param) {
            $parameters[] = $param;
        }

        return $parameters;
    }
}
