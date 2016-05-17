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

class SuggestType extends AbstractType
{
    /**
     * @var SuggesterRegistry
     */
    private $registry;

    public function __construct(SuggesterRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $suggester = $this->registry->get($options['suggester']);
        $builder->addViewTransformer(new SuggestTransformer($suggester, $options['multiple']));
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $suggesterName = $options['suggester'];

        // add "entity_suggest" block prefixes to simplify form styling
        $pos = array_search($this->getBlockPrefix(), $view->vars['block_prefixes']);
        array_splice($view->vars['block_prefixes'], $pos + 1, 0, $suggesterName . '_suggest');

        $ids = array_map(function ($item) {
            return $item['id'];
        }, $view->vars['value']);

        $view->vars = array_merge([
            'ids' => $ids,
            'multiple' => $options['multiple'],
            'suggester_name' => $suggesterName,
            'widget' => $options['widget'],
            'extra' => $options['extra']
        ], $view->vars);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('suggester');

        $resolver->setDefaults(array_merge([
            'widget' => 'select2_v3',
            'compound' => false,
            'multiple' => false,
            'extra' => []
        ]));
    }
}
