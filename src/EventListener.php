<?php

declare(strict_types=1);

namespace cooldogedev\BedrockEconomyScore;

use cooldogedev\BedrockEconomy\BedrockEconomy;
use cooldogedev\BedrockEconomy\database\cache\GlobalCache;
use cooldogedev\BedrockEconomy\event\transaction\AddTransactionEvent;
use cooldogedev\BedrockEconomy\event\transaction\SetTransactionEvent;
use cooldogedev\BedrockEconomy\event\transaction\SubtractTransactionEvent;
use cooldogedev\BedrockEconomy\event\transaction\TransferTransactionEvent;
use Ifera\ScoreHud\event\PlayerTagsUpdateEvent;
use Ifera\ScoreHud\event\TagsResolveEvent;
use Ifera\ScoreHud\scoreboard\ScoreTag;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\player\Player;

final class EventListener implements Listener
{
    public function __construct(protected readonly BedrockEconomyScore $plugin) {}

    protected function updateTags(Player $player): void
    {
        $cache = GlobalCache::ONLINE()->get($player->getName());

        if ($cache === null) {
            return;
        }

        $event = new PlayerTagsUpdateEvent($player, [
            new ScoreTag(BedrockEconomyTags::TAG_AMOUNT, (string)$cache->amount),
            new ScoreTag(BedrockEconomyTags::TAG_DECIMALS, (string)$cache->decimals),
            new ScoreTag(BedrockEconomyTags::TAG_POSITION, number_format($cache->position)),
            new ScoreTag(BedrockEconomyTags::TAG_BALANCE, BedrockEconomy::getInstance()->getCurrency()->formatter->format($cache->amount, $cache->decimals)),
        ]);
        $event->call();
    }

    public function onPlayerJoin(PlayerJoinEvent $event): void
    {
        $this->updateTags($event->getPlayer());
    }

    public function onAddTransaction(AddTransactionEvent $event): void
    {
        $player = $this->plugin->getServer()->getPlayerExact($event->username);

        if ($player === null) {
            return;
        }

        $this->updateTags($player);
    }

    public function onSubtractTransaction(SubtractTransactionEvent $event): void
    {
        $player = $this->plugin->getServer()->getPlayerExact($event->username);

        if ($player === null) {
            return;
        }

        $this->updateTags($player);
    }

    public function onSetTransaction(SetTransactionEvent $event): void
    {
        $player = $this->plugin->getServer()->getPlayerExact($event->username);

        if ($player === null) {
            return;
        }

        $this->updateTags($player);
    }

    public function onTransfer(TransferTransactionEvent $event): void
    {
        foreach ([$event->source, $event->target] as $party) {
            $player = $this->plugin->getServer()->getPlayerExact($party["username"]);

            if ($player === null) {
                continue;
            }

            $this->updateTags($player);
        }
    }

    public function onTagResolve(TagsResolveEvent $event): void
    {
        $player = $event->getPlayer();
        $tag = $event->getTag();
        $cache = GlobalCache::ONLINE()->get($player->getName());

        if ($cache === null) {
            return;
        }

        match ($tag->getName()) {
            BedrockEconomyTags::TAG_AMOUNT => $tag->setValue((string)$cache->amount),
            BedrockEconomyTags::TAG_DECIMALS => $tag->setValue((string)$cache->decimals),
            BedrockEconomyTags::TAG_POSITION => $tag->setValue(number_format($cache->position)),
            BedrockEconomyTags::TAG_BALANCE => $tag->setValue(BedrockEconomy::getInstance()->getCurrency()->formatter->format($cache->amount, $cache->decimals)),

            default => null,
        };
    }
}
