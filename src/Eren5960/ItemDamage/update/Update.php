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
 * @date 17 Mart 2020
 */
declare(strict_types=1);
 
namespace Eren5960\ItemDamage\update;
 
use pocketmine\plugin\PluginDescription;
use pocketmine\Server;
use pocketmine\utils\Internet;

class Update{
	/** @var PluginDescription */
	public $data;

    public function __construct(PluginDescription $data){
		$this->data = $data;
    }

    public function needUpdate(): bool{
	    $url = "https://raw.githubusercontent.com/Eren5960/" . $this->data->getName() . "/master/plugin.yml";
	    $current_version = $this->data->getVersion();

	    $err = '';
	    $content = Internet::getURL($url, 10, [], $err);
	    if(empty($err)){
		    if(substr($content, 0, 3) !== "404"){
			    $version = yaml_parse($content)["version"];
			    return floatval($version) > floatval($current_version);
		    }
	    }else{
		    Server::getInstance()->getLogger()->debug(substr($err, 0, 9) === "Could not" ? "You haven't internet connection for check " . $this->data->getName() . " version." : $err);
	    }

	    return false;
    }

	public function check(): void{
		if($this->needUpdate()){
			Server::getInstance()->getLogger()->alert("ItemDamage new version is available!");
			Server::getInstance()->getLogger()->alert("Download from §7https//github.com/§eeren5960§7/§6" . $this->data->getName() . "§c the new version.");
		}
	}
}