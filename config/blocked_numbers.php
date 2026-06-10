<?php

return [
    /*
    | When false, JazzCash skips blocked_numbers middleware enforcement and does not
    | record new blocks (insufficient balance, manual cancellation) or post-success cooldown.
    */
    'jazzcash_blocking_enabled' => false,

    /*
    | Minutes to block a phone after a successful payin (same number cannot pay again until elapsed).
    */
    'post_success_cooldown_minutes' => 3,
];
