<?php

abstract class DisplayCard {
    protected string $title = '';
    protected array $fields = []; // Optional structured data for child class use

    abstract public function render(): string;

    protected function wrap(string $body): string {
        return "<div class='card'>{$body}</div>";
    }
}
