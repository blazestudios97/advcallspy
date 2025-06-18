<?php
namespace FreePBX\modules;
use FreePBX\Module\Base;
use PDO;
/*
 * Class stub for BMO Module class
 * In _Construct you may remove the database line if you don't use it
 * In getActionbar change extdisplay to align with whatever variable you use to decide if the page is in edit mode.
 *
 */

class Advcallspy extends \FreePBX_Helpers implements \BMO {
	private $FreePBX;
	private $Database;
	public function __construct($freepbx = null) {
		if ($freepbx == null) {
			throw new Exception("Not given a FreePBX Object");
		}
		$this->FreePBX = $freepbx;
		$this->Database = $freepbx->Database;
	}
	//Install method. use this or install.php using both may cause weird behavior
	public function install() 
    {
        $fcc = new \featurecode('core', 'chanspy');
        // disable default ChanSpy feature code, if enabled.
        \outn(_("Checking if default ChanSpy feature code is enabled.. "));
        if($fcc->isEnabled()) {
            \out(_("ChanSpy feature code is enabled. Disabling it."));
            $fcc->setDefault('*555');
            $fcc->setEnabled(false);
            $fcc->update();
            \out(_("Default ChanSpy feature code is disabled"));
        }
        unset($fcc);
        $exist = $this->getSpycode(555);
        if (is_array($exist)) {
            \out(_("Advanced Call Spy default spy code exists...done"));
            return;
        }
        \out(_("Adding Advanced Call Spy default spy code 555 (ChanSpy). You must enable it for use."));
        $def = [ 
            'spycode' => 555,
            'description' => 'Default Spy Code',
            'spytype' => 'ChanSpy',
            'status' => 'disabled',
            'passcode' => '',
            'recording' => 'no',
            'cycledtmf' => '',
            'exitdtmf' => '',
            'modedtmf' => '',
            'bridged' => 1,
            'qmode' => '',
            'whisper' => '',
            'barge' => '',
            'listen' => '',
            'sayname' => '',
            'skip' => '',
            'stopspy' => '',
            'exithangup' => '',
            'eventlog' => 0,
            'genhint' => 0,
            'restricted' => '',
            'enforcelist' => '',
            'spygroups' => ''
        ];
        $this->setSpycode($def);
    }	
    //Uninstall method. use this or install.php using both may cause weird behavior
	public function uninstall() {}
	//Not yet implemented
	public function backup() {}
	//not yet implimented
	public function restore($backup) {}

    public static function myConfigPageInits() { 
        return array("extensions", "users"); 
    }

	//process form
	public function doConfigPageInit($page) {
        if ($page == 'extensions' || $_REQUEST['display'] == 'extensions')  {
            if(isset($_POST['spygroup'])) {
               // print_r($_POST['spygroup']);
            } 
        }
        if($_REQUEST['display'] == 'advcallspy') {
            if(isset($_REQUEST['form_action'])) {
                if($_REQUEST['form_action']== 'add') {
                    $this->setSpycode($_POST);
                }
                if($_REQUEST['form_action'] == 'edit') {
                    $this->editSpycode($_POST);
                }
            }
            if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete') {
                $this->delSpycode($_REQUEST['extdisplay']);
            }
        }
        if($_REQUEST['display'] == 'spygroups') {
            if(isset($_REQUEST['form_action'])) {
                $this->setSpygroup($_POST);
            }
            if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'delete') {
                $this->delSpygroup($_REQUEST['extdisplay']);
            }
        }
    }
    public static function myGuiHooks() {
        return array("core");
    }
    /**
     * Do GUI Hook
     * Hook into various module GUI interfaces.
     */
    public function doGuiHook(&$cc, $page) 
    {
        // Catch the extensions form
        if($_REQUEST['display'] == 'extensions' && isset($_REQUEST['extdisplay']) && !empty($_REQUEST['extdisplay']))  {
            $elems = $this->extenGroupElem($_REQUEST['extdisplay']);
            
            $cc->addguielem("Extension Options", 
                            new \gui_multiselectbox('spygroups', $elems[0], $elems[1], 'Target of Spy Group(s)',
                            'Select the Spy Groups the extension should be a target in',false,'',false,'multiple'),
                            'advanced');
                            
            $jshook = "<script>
                        $( function() {
                            $('.multiple').multiselect({
                                enableFiltering: true, includeSelectAllOption: true, enableCaseInsensitiveFiltering: true, buttonWidth: '300px',
                            });
                        });
                        </script>";

            $cc->addguielem('Extension Options', new \gui_html('spygroupsjs', $jshook), 'advanced');

        }
    }
    /**
     * Check Extension Map Hook
     * @param bool|array $exten
     * @return array $extenlist 
     */
    public function checkExtMap($exten=true) {
        $extenlist = array();
        if (is_array($exten) && empty($exten)) {
            return $extenlist;
        }
        $sql = "SELECT spycode, description FROM advcallspy_details";
        if (is_array($exten)) {
            $sql .= "WHERE spycode in ('".implode("','",$exten)."')";
        }
        $sql .= " ORDER BY spycode";
        $results = sql($sql,"getAll",DB_FETCHMODE_ASSOC);
    
        foreach ($results as $result) {
            $thisexten = $result['spycode'];
            $extenlist[$thisexten]['description'] = _("Advanced Call Spy: ").$result['description'];
            $extenlist[$thisexten]['status'] = 'INUSE';
            $extenlist[$thisexten]['edit_url'] = 'config.php?display=advcallspy&extdisplay='.urlencode($thisexten);
        }
        return $extenlist;
    }
	//This shows the submit buttons
	public function getActionBar($request) {
		$buttons = array();
		switch($_GET['display']) {
			case 'advcallspy':
                case 'spygroups':
				$buttons = array(
					'delete' => array(
						'name' => 'delete',
						'id' => 'delete',
						'value' => _('Delete')
					),
					'reset' => array(
						'name' => 'reset',
						'id' => 'reset',
						'value' => _('Reset')
					),
					'submit' => array(
						'name' => 'submit',
						'id' => 'submit',
						'value' => _('Submit')
					)
				);
				if (empty($_GET['view'])) {
					unset($buttons['delete']);
                    $buttons = [];
				}
			break;
		}
		return $buttons;
	}
	public function showPage($page = null)
    {
        $page = $page == 'advcallspy' ? 'spycodes' : $page;
        $action = !empty($_REQUEST['view']) ? $_REQUEST['view'] : "";
        $vars = [];
        
            switch($action) {
                case 'form': 
                    return load_view(__DIR__.'/views/' . $page . '/form.php',$vars);
                    break;
                default:
                    return load_view(__DIR__.'/views/' . $page . '/grid.php',$vars);
            }
     
       // $action = !empty($_REQUEST['action']) ? $_REQUEST['action'] : "";
        
		return;
        
	}
	public function ajaxRequest($req, &$setting) {
		switch ($req) {
			case 'getJSON':
				return true;
			break;
			default:
				return false;
			break;
		}
	}
	public function ajaxHandler(){
		switch ($_REQUEST['command']) {
			case 'getJSON':
				switch ($_REQUEST['jdata']) {
					case 'grid':
						/*code here to generate array*/
                        return $this->listAll();
					break;
                    case 'scgrid':
						/*code here to generate array*/
                        $codes = $this->listAll();
                        $rdata = [];
						foreach($codes as $sc){
						    $rdata[] = ['spycode' => $sc['spycode'] . ' - ' . $sc['description'], 'exten' => $sc['spycode']];
						}
						return $rdata;
					break;
                    case 'sggrid':
						/*code here to generate array*/
                        $codes = $this->listAllGroups();
                        $rdata = [];
						foreach($codes as $sc){
						    $rdata[] = ['spygroup' => $sc['spygroup'] . ' - ' . $sc['description'], 'exten' => $sc['spygroup']];
						}
						return $rdata;
					break;
                    case 'groupGrid':
						$ret = array();
						/*code here to generate array*/
                        return $this->listAllGroups();
						return $ret;
					break;
					default:
						return false;
					break;
				}
			break;

			default:
				return false;
			break;
		}
	}
    public function listAll()
    {
		$sql = "SELECT * FROM advcallspy_details";
		$stmt = $this->FreePBX->Database->prepare($sql);
		$stmt->execute();
		$codes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $all_codes = [];
        if(!empty($codes)) {
            foreach($codes as $code) {
                $new['spycode'] = $code['spycode'];
                $new['spytype'] = $code['spytype'];
                $new['description'] = $code['description'];
                $new['status'] = $code['status'];
                $all_codes[] = $new;
            }
        }
        return $all_codes;
	}
    /**
     * List All Groups
     * get an list of all spy groups
     * 
     * @return array $all_groups an multidimensional array of of spy groups
     */
    public function listAllGroups()
    {
		$sql = "SELECT * FROM advcallspy_groups";
		$stmt = $this->FreePBX->Database->prepare($sql);
		$stmt->execute();
		$groups = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $all_groups = [];
        if(!empty($groups)) {
            foreach($groups as $grp) {
                $new['spygroup'] = $grp['spygroup'];
                $new['description'] = $grp['description'];
                $all_groups[] = $grp;
            }
        }
        return $all_groups;
	}
    public function listGroups()
    {
		$sql = "SELECT * FROM advcallspy_groups";
		$stmt = $this->FreePBX->Database->prepare($sql);
		$stmt->execute();
		$codes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $all_groups = [];
        if(!empty($codes)) {
            foreach($codes as $code) {
                $all_groups[] = [$code['spygroup'], $code['description']];
            }
        }
        return $all_groups;
	}
    public function listGroupExtens($group = null){
		$sql = "SELECT * FROM advcallspy_group_extens";
        if (!is_null($group)) {
            $sql .= " WHERE spygroup='{$group}'";
        }
		$stmt = $this->FreePBX->Database->prepare($sql);
		$stmt->execute();
		$codes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $all_codes = [];
        if(!empty($codes)) {
            foreach($codes as $code) {
                $new[] = $code['exten'];
                $all_codes[] = $code['exten'];
            }
        }
        return $all_codes;
	}
    public function listAllExtenGroups(){
		$sql = "SELECT * FROM advcallspy_group_extens";
        
		$stmt = $this->FreePBX->Database->prepare($sql);
		$stmt->execute();
		$codes = $stmt->fetchAll(PDO::FETCH_ASSOC);
       
        $all_codes = [];
        if(!empty($codes)) {
            foreach($codes as $code) {
                $new = [];
                $new[$code['exten']][] = $code['spygroup'];
                $all_codes[] = $new;
            }
        }
        //sort($all_codes);
        return $all_codes;
	}
    public function listExtenGroups($exten){
		$sql = "SELECT spygroup FROM advcallspy_group_extens WHERE exten='{$exten}'";
       
		$stmt = $this->FreePBX->Database->prepare($sql);
		$stmt->execute();
		$codes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $all_codes = [];
        if(!empty($codes)) {
            foreach($codes as $code) {
                $all_codes[] = $code['spygroup'];
            }
        }
        return $all_codes;
	}
    /**
     * Get Spy Codes
     * gets full details of all the spy codes
     */
    public function getSpycodes($enabled = false)
    {
		$sql = "SELECT * FROM advcallspy_details";
        if ($enabled) {
            $sql .= " WHERE status='enabled'";
        }
		$stmt = $this->FreePBX->Database->prepare($sql);
		$stmt->execute();
		$codes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $all_codes = [];
        if(!empty($codes)) {
            foreach($codes as $code) {
                $all_codes[] = $code ;
            }
        }
        return $all_codes;
	}
    /**
     * Get Spy Code
     */
    public function getSpycode($code)
    {
        $sql = "SELECT * FROM advcallspy_details WHERE spycode = ?";
        $stmt = $this->FreePBX->Database->prepare($sql);
		$stmt->execute([$code]);
		$code = $stmt->fetch(PDO::FETCH_ASSOC);
        return $code;
    }
    /**
     * Get Spy Group
     */
    public function getSpygroup($group)
    {
        $sql = "SELECT * FROM advcallspy_groups WHERE spygroup = ?";
        $stmt = $this->FreePBX->Database->prepare($sql);
		$stmt->execute([$group]);
		$code = $stmt->fetch(PDO::FETCH_ASSOC);
        return $code;
    }
    /**
     * Set Spy Code 
     * adds the spy code to the database. If the spy code already exists, replace it.
     * if this is a new spy code, add a CustomDevstate for it in AstDB.
     * 
     * @param array $vars array of data for the spy code
     */
    public function setSpycode($vars)
    {
        $restricted = '';
        $enforcelist = '';
        $spygroups = '';
        if (!empty($vars['restricted'])) {
            $restricted = implode('-', $vars['restricted']);
        }
        if (!empty($vars['enforced'])) {
            $enforcelist = implode(':', $vars['enforced']);
        }
        if (!empty($vars['spygroups'])) {
            $spygroups = implode(':', $vars['spygroups']);
        }
        if ($vars['pinset'] == '') {
            $vars['pinset'] = 0;
        }
        $sql = "REPLACE INTO advcallspy_details(
            spycode, `description`, spytype, status, passcode, pinset, recording, cycledtmf,
            exitdtmf, modedtmf, bridged, qmode, whisper, barge, listen, sayname,
            skip, stopspy, exithangup, eventlog, genhint, restricted, enforcelist, spygroups
        ) VALUES (
            :spycode, :description, :spytype, :status, :passcode, :pinset, :recording, :cycledtmf,
            :exitdtmf, :modedtmf, :bridged, :qmode, :whisper, :barge, :listen, :sayname,
            :skip, :stopspy, :exithangup, :eventlog, :genhint, :restricted, :enforcelist, :spygroups
        )";
        
        $stmt = $this->Database->prepare($sql);
        $stmt->execute([
            ':spycode' => $vars['spycode'],
            ':description' => $vars['description'],
            ':spytype' => $vars['spytype'],
            ':status' => $vars['status'],
            ':passcode' => $vars['passcode'],
            ':pinset' => $vars['pinset'],
            ':recording' => 'no',
            ':cycledtmf' => $vars['cycledtmf'],
            ':exitdtmf' => $vars['exitdtmf'],
            ':modedtmf' => $vars['modedtmf'],
            ':bridged' => $vars['bridged'],
            ':qmode' => $vars['qmode'],
            ':whisper' => $vars['whisper'],
            ':barge' => $vars['barge'],
            ':listen' => $vars['listen'],
            ':sayname' => $vars['sayname'],
            ':skip' => $vars['skip'],
            ':stopspy' => $vars['stopspy'],
            ':exithangup' => $vars['exithangup'],
            ':eventlog' => $vars['eventlog'],
            ':genhint' => $vars['genhint'],
            ':restricted' => $restricted,
            ':enforcelist' => $enforcelist,
            ':spygroups' => $spygroups
        ]);
        
        
        if($stmt->errorInfo()) {
            //throw new \Exception("Query error: " . json_encode($stmt->errorInfo()));
        }
        if($vars['status'] == 'enabled') {
            needreload();
        }
        
        $response = $this->FreePBX->astman->send_request('Command',['Command'=>"devstate change Custom:SPYCODE".$vars['spycode']." NOT_INUSE"]);
    }
     /**
     * Set Spy Code 
     * adds the spy code to the database. If the spy code already exists, replace it.
     * if this is a new spy code, add a CustomDevstate for it in AstDB.
     * 
     * @param array $vars array of data for the spy code
     */
    public function editSpycode($vars)
    {
        $spiers = '';
        $enforcelist = '';
        $spygroups = '';
        if (!empty($vars['restricted'])) {
            $spiers = implode('-', $vars['spiers']);
        }
        if (!empty($vars['enforced'])) {
            $enforcelist = implode(':', $vars['enforced']);
        }
        if (!empty($vars['spygroups'])) {
            $spygroups = implode(':', $vars['spygroups']);
        }
        if ($vars['pinset'] == '') {
            $vars['pinset'] = 0;
        }
        $sql = "UPDATE advcallspy_details SET 
            spycode = :spycode,
            `description` = :description,
            spytype = :spytype, 
            status = :status, 
            passcode = :passcode, 
            pinset = :pinset, 
            recording = :recording, 
            cycledtmf = :cycledtmf,
            exitdtmf = :exitdtmf, 
            modedtmf = :modedtmf, 
            bridged = :bridged, 
            qmode = :qmode, 
            whisper = :whisper, 
            barge = :barge, 
            listen = :listen, 
            sayname = :sayname,
            skip = :skip, 
            stopspy = :stopspy, 
            exithangup = :exithangup, 
            eventlog = :eventlog, 
            genhint = :genhint, 
            restricted = :restricted, 
            enforcelist = :enforcelist, 
            spygroups = :spygroups WHERE spycode_id = :spycode_id";
        
        $stmt = $this->Database->prepare($sql);
        $stmt->execute([
            ':spycode' => $vars['spycode'],
            ':description' => $vars['description'],
            ':spytype' => $vars['spytype'],
            ':status' => $vars['status'],
            ':passcode' => $vars['passcode'],
            ':pinset' => $vars['pinset'],
            ':recording' => 'no',
            ':cycledtmf' => $vars['cycledtmf'],
            ':exitdtmf' => $vars['exitdtmf'],
            ':modedtmf' => $vars['modedtmf'],
            ':bridged' => $vars['bridged'],
            ':qmode' => $vars['qmode'],
            ':whisper' => $vars['whisper'],
            ':barge' => $vars['barge'],
            ':listen' => $vars['listen'],
            ':sayname' => $vars['sayname'],
            ':skip' => $vars['skip'],
            ':stopspy' => $vars['stopspy'],
            ':exithangup' => $vars['exithangup'],
            ':eventlog' => $vars['eventlog'],
            ':genhint' => $vars['genhint'],
            ':restricted' => $spiers,
            ':enforcelist' => $enforcelist,
            ':spygroups' => $spygroups,
            ':spycode_id' => $vars['spycode_id']
        ]);
        
        
        if($stmt->errorInfo()) {
            //throw new \Exception("Query error: " . json_encode($stmt->errorInfo()));
        }
        if($stmt->rowCount() > 0) {
            needreload();
        }
        
        
    }
    /**
     * Set Spy Group
     * adds the spy target group to the database. If the spy group already exists, replace it.
     * 
     * 
     * 
     * @param array $vars array of data for the spy group
     */
    public function setSpygroup($vars)
    {
        $sql = "REPLACE INTO advcallspy_groups(spygroup, `description`) 
                VALUES (:spygroup, :description)";
        $stmt = $this->Database->prepare($sql);
        $stmt->execute([
            ':spygroup' => $vars['spygroup'],
            ':description' => $vars['description']
        ]);
        $this->setGroupExtens($vars['spygroup'], $vars['targets'] ?? []);
    }
    /**
     * Set Group Extens
     * removes all existing extensions for the group and 
     * adds selected extensions into the database
     * 
     * @param string $group spygroup name
     * @param array $extens an array of extensions associated to the group
     */
    public function setGroupExtens($group, $extens)
    {
        $sql = "DELETE FROM advcallspy_group_extens WHERE spygroup='" . $group . "'";
        $stmt = $this->Database->prepare($sql);
        $stmt->execute();
        foreach($extens as $exten) {
            $sql = "INSERT INTO advcallspy_group_extens (spygroup, exten) VALUES(:spygroup, :exten)";
            $stmt = $this->Database->prepare($sql);
            $stmt->execute([
                ':spygroup' => $group,
                ':exten' => $exten
            ]);
        }
    }
    /**
     * Set Exten Groups
     * Sets the groups by extension
     */
    public function setExtenGroups($group, $extens, $delOnly = false)
    {
        $sql = "DELETE FROM advcallspy_group_extens WHERE exten='" . $exten . "'";
        $stmt = $this->Database->prepare($sql);
        $stmt->execute();
        foreach($extens as $exten) {
            $sql = "INSERT INTO advcallspy_group_extens (spygroup, exten) VALUES(:spygroup, :exten)";
            $stmt = $this->Database->prepare($sql);
            $stmt->execute([
                ':spygroup' => $group,
                ':exten' => $exten
            ]);
        }
    }
    public function extenGroupElem($exten)
    {
        $groupslist = $this->listGroups();
        $extengrps = $this->listExtenGroups($exten);
        $groups_list = [];
        
        foreach ($groupslist as $result) {
           $groups_list[] = ['value' => $result[0], 'text' => $result[0] . ' (' . $result[1] . ')'];
        }
       
        return [$groups_list, $extengrps];
        
    }
    /**
     * Delete Spy Code
     * deletes existing spy code
     */
    public function delSpycode($spycode)
    {
        $sql = "DELETE FROM advcallspy_details WHERE spycode = ?";
        $stmt = $this->FreePBX->Database->prepare($sql);
		$stmt->execute([$spycode]);
        if($stmt->errorInfo()) {
            //throw new \Exception("Query error: " . json_encode($stmt->errorInfo()));
        }
        needreload();
    }
    /**
     * Delete Spy Group
     * deletes existing spy group
     */
    public function delSpygroup($spygroup)
    {
        $sql = "DELETE FROM advcallspy_groups WHERE spygroup = ?";
        $stmt = $this->FreePBX->Database->prepare($sql);
		$stmt->execute([$spygroup]);
        if($stmt->errorInfo()) {
            //throw new \Exception("Query error: " . json_encode($stmt->errorInfo()));
        }
        $sql = "DELETE FROM advcallspy_group_extens WHERE spygroup='" . $spygroup . "'";
        $stmt = $this->Database->prepare($sql);
        $stmt->execute();
        needreload();
    }
	
    public function getRightNav($request) {
       
        $page = $request['display'];
        $page = $page == 'advcallspy' ? 'spycodes' : $page;
        if(isset($_GET['view']) && $_GET['view'] == 'form'){
		    return load_view(__DIR__."/views/" . $page . "/rnav.php",[]);
		}
		
	}
    
    public function myDialplanHooks(){
		return 650;
	}
    /**
     * Dialplan Generation Hook
     * 
     */
    public function doDialplanHook(&$ext, $engine, $priority)
    {
        global $version, $amp_conf, $astman;

        // get all the enabled spy codes
        $codes = $this->getSpycodes(true);
        if (!$codes) {
            return;
        }
        // set context name
        $context = 'app-callspy';
        $ext->addInclude('from-internal-additional', 'app-callspy');
        
        foreach ($codes as $scode) {
            $opts = '';
            $spycode = $scode['spycode'];
            $opts .= $scode['bridged'] === 1 ? 'b' : '';
            $opts .= !empty($scode['modedtmf']) ? $scode['modedtmf'] : '';
            $opts .= !empty($scode['barge']) ? $scode['barge'] : '';
            $opts .= !empty($scode['listen']) ? $scode['listen'] : '';
            $opts .= !empty($scode['whisper']) ? $scode['whisper'] : '';
            $opts .= !empty($scode['skip']) ? $scode['skip'] : '';
            $opts .= $scode['exithangup'];
            $opts .= $scode['stopspy'];
            $opts .= $scode['sayname'] != '' ? 'n' : '';
            $opts .= $scode['cycledtmf'] != '' ? 'c(' . $scode['cycledtmf'] . ')' : '';
            $opts .= $scode['exitdtmf'] != '' ? 'x(' . $scode['exitdtmf'] . ')' : '';
            $opts .= $scode['enforcelist'] != '' ? 'e(' . $scode['enforcelist'] . ')' : '';
            $opts .= $scode['spygroups'] != '' ? 'g(' . $scode['spygroups'] . ')' : '';

            $ext->add($context, $spycode, '', new \ext_noop('Advanced Call Spy for ${EXTEN}'));
            $ext->add($context, $spycode, '', new \ext_gosub(1, 's', 'macro-user-callerid'));
            $ext->add($context, $spycode, '', new \ext_set('SPYCODE','${EXTEN}'));
            $ext->add($context, $spycode, '', new \ext_set('SPIER','${AMPUSER}'));
            
            if ($scode['restricted'] != '') {
                $ext->add($context, $spycode, '', new \ext_set('SPIERS', $scode['restricted']));
                $ext->add($context, $spycode, '', new \ext_set('SCOUNT', '${FIELDQTY(SPIERS,-)}'));
                $ext->add($context, $spycode, 'spiers', new \ext_while('$[${SCOUNT} > 0]'));
                $ext->add($context, $spycode, '', new \ext_set('SPYMEM','${CUT(SPIERS,-,${SCOUNT})}'));
                $ext->add($context, $spycode, '', new \ext_gotoif('$["${AMPUSER}"="${SPYMEM}"]', ($scode['passcode'] !='' || $scode['pinset'] > 0 ? 'doauth' : 'dospy')));
                $ext->add($context, $spycode, '', new \ext_set('SCOUNT','$[${SCOUNT} - 1]'));
                $ext->add($context, $spycode, '', new \ext_endwhile());
            }
            
            if ($scode['passcode'] != '' && $scode['pinset'] == '') {
                $ext->add($context, $spycode, 'doauth', new \ext_authenticate($scode['passcode']));
            }
            if ($scode['pinset'] > 0) {
                $ext->add($context, $spycode, 'doauth', new \ext_gosub(1, 's', 'macro-pinsets', $scode['pinset'] . ',0'));
            }
            if($scode['genhint']) {
                $ext->add($context, $spycode, '', new \ext_set('DEVICE_STATE(CUSTOM:SPYCODE'.$spycode.')','INUSE'));
            }
            
            if ($scode['eventlog']) {
                $ext->add($context, $spycode, '', new \ext_set('SPYSTART', '${STRFTIME(${EPOCH},,%Y-%m-%d %H:%M:%S)}'));
                $ext->add($context, $spycode, '', new \extension('CELGenUserEvent(SPY_START,spier=${SPIER},spycode=${SPYCODE},time=${SPYSTART})'));
            }
            if ($scode['genhint'] || $scode['eventlog']) {
                $ext->add($context, $spycode, '', new \extension('Set(CHANNEL(hangup_handler_push)=app-callspy,exit,1())'));
                
            }
            $ext->add($context, $spycode, 'dospy', new \ext_answer());
            if($scode['spytype'] == 'ChanSpy') {
                $ext->add($context, $spycode, '', new \ext_chanspy('PJSIP/', $opts));
            } else {
                $ext->add($context, $spycode, '', new \extension('ExtenSpy(,'.$opts.')'));
            }
            $ext->add($context, $spycode, '', new \ext_noop('Advanced Call Spy Normal Exiting'));
            $ext->add($context, $spycode, '', new \ext_hangup());
            // generate hint for this spy code
            if($scode['genhint']) {
                $ext->addHint($context, $spycode, 'Custom:SPYCODE'.$spycode);
            }
        }
        $ext->add($context, 'exit', '', new \ext_noop('Exiting Call Spy ${SPYCODE} ${SPIER}'));
		if ($scode['eventlog']) {
            $ext->add($context, 'exit', '', new \ext_set('SPYEND', '${STRFTIME(${EPOCH},,%Y-%m-%d %H:%M:%S)}'));
            $ext->add($context, 'exit', '', new \extension('CELGenUserEvent(SPY_EXIT,spier=${SPIER},spycode=${SPYCODE},time=${SPYEND})'));
        }
        if($scode['genhint']) {
            $ext->add($context, 'exit', '', new \ext_set('DEVICE_STATE(CUSTOM:SPYCODE${SPYCODE})','NOT_INUSE'));
        }
        $ext->add($context, 'exit', '', new \ext_return());
        $extspygroups = $this->listAllExtenGroups();
        foreach($extspygroups as $idx => $spytargets) {
            foreach($spytargets as $target => $spygroups) {
                $ext->splice('ext-local', $target, 2, new \ext_set('SPYGROUP', implode(",", $spygroups)));
            }
        }

    }
}
