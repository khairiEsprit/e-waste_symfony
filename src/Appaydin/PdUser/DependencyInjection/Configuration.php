<?php


namespace App\Appaydin\PdUser\DependencyInjection;

use App\Appaydin\PdUser\Form\RegisterType;
use App\Appaydin\PdUser\Form\ResettingPasswordType;
use App\Appaydin\PdUser\Form\ResettingType;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('pd_user');
        $rootNode = $treeBuilder->getRootNode();

        // Set Configuration
        $rootNode
            ->children()
                ->scalarNode('user_class')->defaultValue('')->end()
                ->scalarNode('group_class')->defaultValue('')->end()
                ->scalarNode('default_group')->defaultValue('')->end()
                ->scalarNode('login_redirect')->defaultValue('/')->end()
                ->booleanNode('email_confirmation')->defaultFalse()->end()
                ->booleanNode('welcome_email')->defaultTrue()->end()
                ->booleanNode('user_registration')->defaultTrue()->end()
                ->scalarNode('template_path')->defaultValue('@PdUser')->end()
                ->integerNode('resetting_request_time')->defaultValue(7200)
                    ->beforeNormalization()->ifString()->then(static function ($val) { return (int) $val; })->end()
                ->end()
                ->scalarNode('mail_sender_address')->defaultValue('example@example.com')->end()
                ->scalarNode('mail_sender_name')->defaultValue('pdUser')->end()
                ->arrayNode('active_language')->scalarPrototype()->end()->defaultValue(['en'])->end()
                ->scalarNode('register_type')->defaultValue(RegisterType::class)->end()
                ->scalarNode('resetting_type')->defaultValue(ResettingType::class)->end()
                ->scalarNode('resetting_password_type')->defaultValue(ResettingPasswordType::class)->end()
            ->end();

        return $treeBuilder;
    }
}
