<?php
/**
 *  _______                   _______ _______ _______  _____
 * (_______)                 (_______|_______|_______)(_____)
 *  _____    ____ _____ ____  ______  _______ ______  _  __ _
 * |  ___)  / ___) ___ |  _ \(_____ \(_____  |  ___ \| |/ /| |
 * | |_____| |   | ____| | | |_____) )     | | |___) )   /_| |
 * |_______)_|   |_____)_| |_(______/      |_|______/ \_____/
 *
 * @author Eren5960
 * @link https://github.com/Eren5960
 * @date 16 Mart 2020
 */
declare(strict_types = 1);

namespace Eren5960\ItemDamage;

use Eren5960\ItemDamage\update\Update;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Armor;
use pocketmine\item\Bow;
use pocketmine\item\Durable;
use pocketmine\item\FlintSteel;
use pocketmine\item\Shears;
use pocketmine\item\TieredTool;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class Main extends PluginBase implements Listener{
	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		(new Update($this->getDescription()))->check();
	}

	public function onBreak(BlockBreakEvent $event){
		if($event->isCancelled()) return;

		$player = $event->getPlayer();
		$item = $event->getItem();
		if($item instanceof TieredTool)
			$this->toolProcess($item, $player);
		if($item instanceof FlintSteel)
			$this->steelProcess($item, $player);
		if($item instanceof Shears)
			self::message($player, $item, "Makasın", "makas");
	}

	public function onDamage(EntityDamageEvent $event){
		$player = $event->getEntity();
		if($player instanceof Player && !$event->isCancelled()){
			$inv = $player->getArmorInventory();
			$helmet = $inv->getHelmet();
			$chestplate = $inv->getChestplate();
			$leggings = $inv->getLeggings();
			$boots = $inv->getBoots();

			if($helmet instanceof Armor && self::damage($helmet) <= 40)
				$player->sendTip("§4Kaskın kırılmak üzere!");
			if($chestplate instanceof Armor && self::damage($chestplate) <= 40)
				$player->sendTip("§4Göğüslüğün kırılmak üzere!");
			if($leggings instanceof Armor && self::damage($leggings) <= 40)
				$player->sendTip("§4Pantolonun kırılmak üzere!");
			if($boots instanceof Armor && self::damage($boots) <= 40)
				$player->sendTip("§4Ayakkabın kırılmak üzere!");
		}
	}

	public function onDamageEntity(EntityDamageByEntityEvent $event){
		$player = $event->getDamager();
		if($player instanceof Player && !$event->isCancelled()){
			$tool = $player->getInventory()->getItemInHand();
			if($tool instanceof TieredTool) $this->toolProcess($tool, $player);
			if($tool instanceof Bow) $this->bowProcess($tool, $player);
		}
	}

	public function itemUseEvent(PlayerInteractEvent $event){
		if($event->getAction() === PlayerInteractEvent::RIGHT_CLICK_AIR && !$event->isCancelled()){
			$player = $event->getPlayer();
			$item = $event->getItem();
			if($item instanceof TieredTool) $this->toolProcess($item, $player);
			if($item instanceof Bow) $this->bowProcess($item, $player);
			if($item instanceof FlintSteel) $this->steelProcess($item, $player);
		}
	}

	public function toolProcess(TieredTool $item, Player $player){
		self::message($player, $item, "Aletin", "alet");
	}

	public function bowProcess(Bow $item, Player $player){
		self::message($player, $item, "Yayın", "yay");
	}

	public function steelProcess(FlintSteel $item, Player $player){
		self::message($player, $item, "Çakmağın", "çakmak");
	}

	public static function message(Player $player, Durable $item, string $item_first, string $item_two): void{
		$damage = self::damage($item);
		$player->sendPopup(self::getProgress($damage, $item->getMaxDurability()));

		if($damage < 10){
			if($damage === 0){
				$player->sendMessage("§8» §cElindeki {$item_two} kırıldı!");
			}else{
				$player->sendTip("§4{$item_first} kırılmak üzere!");
			}
		}
	}

	/**
	 * Return the next damage for Durable
	 * @param Durable $item
	 *
	 * @return int
	 */
	public static function damage(Durable $item): int{
		return ($item->getMaxDurability() - $item->getDamage()) - 1;
	}

	public static function getProgress(int $progress, int $size): string {
		$divide = $size > 750 ? 50 : ($size > 500 ? 20 : ($size > 300 ? 15 : ($size > 200 ? 10 : ($size > 100 ? 5 : 3)))); // for short bar
		$percentage = number_format(($progress / $size) * 100, 2);
		$progress = (int) ceil($progress / $divide);
		$size = (int) ceil($size / $divide);

		return TextFormat::GRAY . "[" . TextFormat::GREEN . str_repeat("|", $progress) .
			TextFormat::RED . str_repeat("|", $size - $progress) . TextFormat::GRAY . "] " .
			TextFormat::AQUA . "{$percentage} %%";
	}
}