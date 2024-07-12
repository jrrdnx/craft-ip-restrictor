<?php
/**
 * @link https://jarrodnix.dev/
 * @copyright Copyright (c) Jarrod D Nix
 * @license MIT
 */

namespace jrrdnx\iprestrictor\migrations;

use craft\db\Migration;

/**
 * Installation Migration
 *
 * @author Jarrod D Nix
 * @since 1.0
 */
class Install extends Migration
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        return true;
    }
}
