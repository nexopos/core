<?php
namespace Ns\Classes;

#[\Attribute( \Attribute::TARGET_CLASS )]
class CrudScope
{
    public function __construct(
        public string | array $class,
    ) {}
}