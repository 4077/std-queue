<?php

function queue($instance = 'default')
{
    return new \std\queue\Queue($instance);
}
