<?php

namespace izu\pite;

interface Tokens
{
    const ESCAPE = 1;
    const BLOCK_OPEN = 10;
    const BLOCK_CLOSE = 11;
    const OMIT_OPEN = 12;
    const OMIT_CLOSE = 13;
    const DEFAULT_TO = 20;
    const DEFAULT_SINGLE = 21;
    const GLUE = 22;
    const GLUE_OMIT = 23;
    const PRESERVE = 30;
    const WHITESPACE = 40;
    const ANY = 50;
}