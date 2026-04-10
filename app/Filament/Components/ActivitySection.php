<?php

namespace App\Filament\Components;

use Filament\Infolists\ComponentContainer;
use Illuminate\Database\Eloquent\Model;
use JaOcero\ActivityTimeline\Components\ActivitySection as BaseActivitySection;

class ActivitySection extends BaseActivitySection
{
    /**
     * @return array<ComponentContainer>
     */
    public function getChildComponentContainers(bool $withHidden = false): array
    {
        if ((! $withHidden) && $this->isHidden()) {
            return [];
        }

        $containers = [];

        foreach ($this->getState() ?? [] as $itemKey => $itemData) {
            $container = $this
                ->getChildComponentContainer()
                ->getClone()
                ->statePath($itemKey)
                ->inlineLabel(false);

            if ($itemData instanceof Model) {
                $container->record($itemData);
            } elseif (is_array($itemData)) {
                $container->state($itemData);
            }

            $containers[$itemKey] = $container;
        }

        return $containers;
    }
}
