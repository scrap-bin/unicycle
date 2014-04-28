<?php

namespace R2\Templating;

class PhpEngine implements EngineInterface
{
    /** @var string Where templates are */
    protected $dir;
    /** @var string Templates extension (incl. dot) */
    protected $ext;
    /** @var string[] Nesting templates */
    protected $templates;
    /** @var string[] Named content */
    protected $blocks;
    /** @var string[] Block names stack */
    protected $blockStack;

    /**
     * Constructor
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->dir = isset($config['template_dir']) ? $config['template_dir'] : (__DIR__ . '/views');
        $this->ext = isset($config['template_ext']) ? $config['template_ext'] : '.php';
        $this->blocks = [];
        $this->blockStack = [];
    }

    /**
     * Prepare file to include
     * @param  string $name
     * @return string
     */
    protected function prepare($name)
    {
        return $this->dir . '/' . $name . $this->ext;
    }

    /**
     * Print result of templating
     * @param string $name
     * @param array  $data
     */
    public function render($name, array $data = [])
    {
        echo $this->fetch($name, $data);
    }

    /**
     * Return result of templating
     * @param  string $name
     * @param  array  $data
     * @return string
     */
    public function fetch($name, array $data = [])
    {
        $this->templates[] = $name;
        if (!empty($data)) {
            extract($data);
        }
        while ($_ = array_shift($this->templates)) {
            $this->beginBlock('content');
            require($this->prepare($_));
            $this->endBlock();
        }

        return $this->blocks['content'];
    }

    /**
     * Is template file exists?
     * @param  string  $name
     * @return Boolean
     */
    public function exists($name)
    {
        return file_exists($this->prepare($name));
    }

    /**
     * Define parent
     * @param string $name
     */
    protected function extend($name)
    {
        $this->templates[] = $name;
    }

    /**
     * Return content of block if exists
     * @param  string      $name
     * @return string|NULL
     */
    protected function block($name)
    {
        return array_key_exists($name, $this->blocks) ? $this->blocks[$name] : null;
    }

    /**
     * Block begins
     * @param string $name
     */
    protected function beginBlock($name)
    {
        array_push($this->blockStack, $name);
        ob_start();
    }

    /**
     * Block ends
     */
    protected function endBlock()
    {
        $name = array_pop($this->blockStack);
        $this->blocks[$name] = ob_get_clean();
    }
}
