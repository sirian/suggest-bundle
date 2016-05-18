<?php

namespace Sirian\SuggestBundle\Form\Type;

use Sirian\SuggestBundle\Form\DataTransformer\SuggestTransformer;
use Sirian\SuggestBundle\Suggest\SuggesterInterface;
use Sirian\SuggestBundle\Suggest\SuggesterRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SuggestType extends AbstractType
{
    /**
     * @var SuggesterRegistry
     */
    private $registry;
    /**
     * @var array
     */
    private $defaultOptions;

    public function __construct(SuggesterRegistry $registry, array $defaultOptions = [])
    {
        $this->registry = $registry;
        $this->defaultOptions = $defaultOptions;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $suggester = $this->registry->get($options['suggester']);
        $builder->addViewTransformer(new SuggestTransformer($suggester, $options['multiple']));
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $alias = $this->registry->getAlias($options['suggester']);

        // add "entity_suggest" block prefixes to simplify form styling
        $pos = array_search($this->getBlockPrefix(), $view->vars['block_prefixes']);
        array_splice($view->vars['block_prefixes'], $pos + 1, 0, $alias . '_suggest');

        $value = $view->vars['value'];

        $values = [];

        if (null !== $value) {
            $values = $options['multiple'] ? $value : [$value];
        }

        $ids = array_map(function ($item) {
            return $item['id'];
        }, $values);


        $view->vars = array_merge([
            'ids' => $ids,
            'values' => $values,
            'multiple' => $options['multiple'],
            'alias' => $alias,
            'widget' => $options['widget'],
            'extra' => $options['extra']
        ], $view->vars);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('suggester');

        $resolver->setDefaults(array_replace([
            'widget' => 'select2_v3',
            'compound' => false,
            'multiple' => false,
            'extra' => []
        ], $this->defaultOptions));
    }

    public function getBlockPrefix()
    {
        return 'suggest';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $this->configureOptions($resolver);
    }


    public function getName()
    {
        return 'suggest';
    }
}
