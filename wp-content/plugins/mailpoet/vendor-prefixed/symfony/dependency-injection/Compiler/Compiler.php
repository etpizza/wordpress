<?php
 namespace MailPoetVendor\Symfony\Component\DependencyInjection\Compiler; if (!defined('ABSPATH')) exit; use MailPoetVendor\Symfony\Component\DependencyInjection\ContainerBuilder; use MailPoetVendor\Symfony\Component\DependencyInjection\Exception\EnvParameterException; class Compiler { private $passConfig; private $log = []; private $serviceReferenceGraph; public function __construct() { $this->passConfig = new \MailPoetVendor\Symfony\Component\DependencyInjection\Compiler\PassConfig(); $this->serviceReferenceGraph = new \MailPoetVendor\Symfony\Component\DependencyInjection\Compiler\ServiceReferenceGraph(); } public function getPassConfig() { return $this->passConfig; } public function getServiceReferenceGraph() { return $this->serviceReferenceGraph; } public function addPass(\MailPoetVendor\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface $pass, $type = \MailPoetVendor\Symfony\Component\DependencyInjection\Compiler\PassConfig::TYPE_BEFORE_OPTIMIZATION, int $priority = 0) { $this->passConfig->addPass($pass, $type, $priority); } public function log(\MailPoetVendor\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface $pass, string $message) { if (\false !== \strpos($message, "\n")) { $message = \str_replace("\n", "\n" . \get_class($pass) . ': ', \trim($message)); } $this->log[] = \get_class($pass) . ': ' . $message; } public function getLog() { return $this->log; } public function compile(\MailPoetVendor\Symfony\Component\DependencyInjection\ContainerBuilder $container) { try { foreach ($this->passConfig->getPasses() as $pass) { $pass->process($container); } } catch (\Exception $e) { $usedEnvs = []; $prev = $e; do { $msg = $prev->getMessage(); if ($msg !== ($resolvedMsg = $container->resolveEnvPlaceholders($msg, null, $usedEnvs))) { $r = new \ReflectionProperty($prev, 'message'); $r->setAccessible(\true); $r->setValue($prev, $resolvedMsg); } } while ($prev = $prev->getPrevious()); if ($usedEnvs) { $e = new \MailPoetVendor\Symfony\Component\DependencyInjection\Exception\EnvParameterException($usedEnvs, $e); } throw $e; } finally { $this->getServiceReferenceGraph()->clear(); } } } 