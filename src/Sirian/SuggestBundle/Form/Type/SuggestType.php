<?php

namespace Sirian\SuggestBundle\Form\Type;

use Sirian\SuggestBundle\Form\DataTransformer\SuggestTransformer;
use Sirian\SuggestBundle\Suggest\Item;
use Sirian\SuggestBundle\Suggest\SuggesterInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SuggestType extends AbstractType
{
    private $suggester;
    private $suggesterName;
    private $name;
    private $defaultOptions;

    public function __construct(SuggesterInterface $suggester, $suggesterName, $name, array $defaultOptions)
    {
        $this->suggester = $suggester;
        $this->name = $name;
        $this->suggesterName = $suggesterName;
        $this->defaultOptions = $defaultOptions;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer(new SuggestTransformer($this->suggester, $options['multiple']));
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $pos = array_search($this->name, $view->vars['block_prefixes']);
        array_splice($view->vars['block_prefixes'], $pos, 0, 'suggest');

        $ids = array_map(function ($item) {
            return $item['id'];
        }, $view->vars['value']);

        $view->vars = array_merge([
            'ids' => $ids,
            'multiple' => $options['multiple'],
            'suggester_name' => $this->suggesterName,
            'extra' => $options['extra']
        ], $view->vars);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array_merge([
            'compound' => false,
            'multiple' => false,
            'extra' => []
        ], $this->defaultOptions));
    }


    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $this->configureOptions($resolver);
    }

    public function getName()
    {
        return $this->name;
    }
}
