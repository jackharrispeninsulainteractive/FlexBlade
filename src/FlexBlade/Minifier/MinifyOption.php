<?php

namespace FlexBlade\Minifier;

enum MinifyOption: string {
    case NO_MINIFY = 'data-minify-skip';
    case MINIFY_ONCE = 'data-minify-once';
    case SCOPE = 'data-minify-scope';
    case MEDIA = 'data-minify-media';
}
