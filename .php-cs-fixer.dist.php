<?php

declare(strict_types=1);

use GD75\DoubleQuoteFixer\DoubleQuoteFixer;
use PhpCsFixer\Config;
use PhpCsFixer\Finder;

if (PHP_SAPI !== "cli") {
    die("This script supports command line usage only. Please check your command.");
}

return (new Config())
    ->setFinder(
        Finder::create()->in(
            [
                "src"
            ]
        )->in(__DIR__)
    )
    ->registerCustomFixers(
        [
            new DoubleQuoteFixer()
        ]
    )
    ->setRiskyAllowed(true)
    ->setRules(
        [
            "GD75/double_quote_fixer" => true,
            "@PSR2" => true,
            "@DoctrineAnnotation" => true,
            "array_syntax" => [
                "syntax" => "short"
            ],
            "blank_line_after_opening_tag" => true,
            "blank_line_before_statement" => [
                "statements" => [
                    "return"
                ]
            ],
            "braces" => [
                "allow_single_line_closure" => true
            ],
            "cast_spaces" => [
                "space" => "none"
            ],
            "compact_nullable_typehint" => true,
            "concat_space" => [
                "spacing" => "one"
            ],
            "declare_strict_types" => true,
            "dir_constant" => true,
            "function_typehint_space" => true,
            "lowercase_cast" => true,
            "method_argument_space" => [
                "on_multiline" => "ensure_fully_multiline"
            ],
            "modernize_types_casting" => true,
            "native_function_casing" => true,
            "new_with_braces" => true,
            "no_alias_functions" => true,
            "no_blank_lines_after_phpdoc" => true,
            "no_empty_phpdoc" => true,
            "no_empty_statement" => true,
            "no_leading_import_slash" => true,
            "no_leading_namespace_whitespace" => true,
            "no_null_property_initialization" => true,
            "no_short_bool_cast" => true,
            "no_singleline_whitespace_before_semicolons" => true,
            "no_superfluous_elseif" => true,
            "no_trailing_comma_in_singleline_array" => true,
            "no_unneeded_control_parentheses" => true,
            "no_unused_imports" => true,
            "no_useless_else" => false,
            "no_whitespace_in_blank_line" => true,
            "ordered_imports" => true,
            "phpdoc_no_access" => true,
            "phpdoc_no_empty_return" => true,
            "phpdoc_no_package" => true,
            "phpdoc_scalar" => true,
            "phpdoc_trim" => true,
            "phpdoc_types" => true,
            "phpdoc_types_order" => [
                "null_adjustment" => "always_last",
                "sort_algorithm" => "none"
            ],
            "return_type_declaration" => [
                "space_before" => "none"
            ],
            "single_trait_insert_per_statement" => true,
            "static_lambda" => true,
            "whitespace_after_comma_in_array" => true
        ]
    );
