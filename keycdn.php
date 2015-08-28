<?php

/**
 * Created by PhpStorm.
 * User: Luke Hardiman
 * Date: 15/08/2015
 * Time: 5:17 PM
 * KeyCDN Module
 *
 * @package blesta
 * @subpackage blesta.components.modules.keycdn
 * @author Luke Hardiman, Webkits
 * @copyright Copyright (c) 2015, Luke Hardiman
 * @license https://raw.githubusercontent.com/webkitz/keycdn/master/LICENSE
 * @link http://webkits.co.nz
 */


class keycdn extends module
{

    /**
     * @var string The version of this module
     */
    private static $version = "0.1.3";
    /**
     * @var string The authors of this module
     */
    private static $authors = array(
        array('name' => "Luke Hardiman", 'url' => "http://webkits.co.nz")
    );
    /**
     * Initializes my little library helper that ive been using with blesta modules
     *
     * @return my_module_lib instance
     */
    private $my_module_lib = false;

    /**
     * Initializes the API and returns a Singleton instance of that object for api calls
     *
     * @param stdClass $module_row A stdClass object representing a single reseller (optional, required when Module::getModuleRow() is unavailable)
     * @return KeyCDN API The
     */
    private $_api = false;

    /**
     * Initializes the module
     */
    public function __construct()
    {
        // Load components required by this module
        Loader::loadComponents($this, array("Input"));

        // Load the language required by this module
        Language::loadLang("keycdn", null, dirname(__FILE__) . DS . "language" . DS);

        //added error reporting internally
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
    }

    /**
     * Returns the name of this module
     *
     * @return string The common name of this module
     */
    public function getName() {
        return Language::_("keycdn.name", true);
    }

    /**
     * Returns the version of this module
     *
     * @return string The current version of this module
     */
    public function getVersion() {
        return self::$version;
    }

    /**
     * Returns the name and URL for the authors of this module
     *
     * @return array A numerically indexed array that contains an array with key/value pairs for 'name' and 'url', representing the name and URL of the authors of this module
     */
    public function getAuthors() {
        return self::$authors;
    }

    /**
     * Returns the value used to identify a particular service
     *
     * @param stdClass $service A stdClass object representing the service
     * @return string A value used to identify this service amongst other similar services
     */
    public function getServiceName($service) {
        foreach ($service->fields as $field) {
            if ($field->key == "keycdn_domain")
                return $field->value;
        }
        return "New";
    }

    /**
     * Returns a noun used to refer to a module row (e.g. "Server", "VPS", "Reseller Account", etc.)
     *
     * @return string The noun used to refer to a module row
     */
    public function moduleRowName() {
        return Language::_("keycdn.module_row", true);
    }

    /**
     * Returns a noun used to refer to a module row in plural form (e.g. "Servers", "VPSs", "Reseller Accounts", etc.)
     *
     * @return string The noun used to refer to a module row in plural form
     */
    public function moduleRowNamePlural() {
        return Language::_("keycdn.module_row_plural", true);
    }

    /**
     * Returns a noun used to refer to a module group (e.g. "Server Group", "Cloud", etc.)
     *
     * @return string The noun used to refer to a module group
     */
    public function moduleGroupName() {
        return null;
    }

    /**
     * Returns the key used to identify the primary field from the set of module row meta fields.
     * This value can be any of the module row meta fields.
     *
     * @return string The key used to identify the primary field from the set of module row meta fields
     */
    public function moduleRowMetaKey() {
        return "account_name";
    }

    /**
     * Performs any necessary bootstraping actions. Sets Input errors on
     * failure, preventing the module from being added.
     *
     * @return array A numerically indexed array of meta data containing:
     * 	- key The key for this meta field
     * 	- value The value for this key
     * 	- encrypted Whether or not this field should be encrypted (default 0, not encrypted)
     */
    public function install() {

    }

    /**
     * Performs migration of data from $current_version (the current installed version)
     * to the given file set version. Sets Input errors on failure, preventing
     * the module from being upgraded.
     *
     * @param string $current_version The current installed version of this module
     */
    public function upgrade($current_version) {

    }

    /**
     * Performs any necessary cleanup actions. Sets Input errors on failure
     * after the module has been uninstalled.
     *
     * @param int $module_id The ID of the module being uninstalled
     * @param boolean $last_instance True if $module_id is the last instance across all companies for this module, false otherwise
     */
    public function uninstall($module_id, $last_instance) {

    }




    /**
     * Returns the value used to identify a particular package service which has
     * not yet been made into a service. This may be used to uniquely identify
     * an uncreated service of the same package (i.e. in an order form checkout)
     *
     * @param stdClass $package A stdClass object representing the selected package
     * @param array $vars An array of user supplied info to satisfy the request
     * @return string The value used to identify this package service
     * @see Module::getServiceName()
     */
    public function getPackageServiceName($packages, array $vars=null) {
        if (isset($vars['account_name']))
            return $vars['account_name'];
        return null;
    }

    /**
     * Attempts to validate service info. This is the top-level error checking method. Sets Input errors on failure.
     *
     * @param stdClass $package A stdClass object representing the selected package
     * @param array $vars An array of user supplied info to satisfy the request
     * @return boolean True if the service validates, false otherwise. Sets Input errors when false.
     */
    public function validateService($package, array $vars=null) {
        // Set rules
        $rules = array(
            'keycdn_domain' => array(
                'format' => array(
                    'rule' => "isEmpty",
                    'negate' => true,
                    'message' => Language::_("keycdn.!error.domain.format", true)
                )
            )
        );

        $this->Input->setRules($rules);
        return $this->Input->validates($vars);
    }

    /**
     * Adds the service to the remote server. Sets Input errors on failure,
     * preventing the service from being added.
     *
     * @param stdClass $package A stdClass object representing the selected package
     * @param array $vars An array of user supplied info to satisfy the request
     * @param stdClass $parent_package A stdClass object representing the parent service's selected package (if the current service is an addon service)
     * @param stdClass $parent_service A stdClass object representing the parent service of the service being added (if the current service is an addon service and parent service has already been provisioned)
     * @param string $status The status of the service being added. These include:
     * 	- active
     * 	- canceled
     * 	- pending
     * 	- suspended
     * @return array A numerically indexed array of meta fields to be stored for this service containing:
     * 	- key The key for this meta field
     * 	- value The value for this key
     * 	- encrypted Whether or not this field should be encrypted (default 0, not encrypted)
     * @see Module::getModule()
     * @see Module::getModuleRow()
     */
    public function addService($package, array $vars=null, $parent_package=null, $parent_service=null, $status="pending") {
        //moved to manually adding service for now
        return array(
            //approval rows
            array(
                'key' => "keycdn_name",   //domain holder for this cdn
                'value' => $vars['keycdn_name'],
                'encrypted' => 0
            ),
            array(
                'key' => "keycdn_domain",   //domain holder for this cdn
                'value' => $vars['keycdn_domain'],
                'encrypted' => 0
            ),
            array(
                'key' => "keycdn_zone_id",   //zone id
                'value' => '',
                'encrypted' => 0
            )
        );
        /*&
        //validate service
        $this->validateService($package, $vars);

        //create zone on keycdn
        $row = $this->getModuleRow($package->module_row);
        $api = $this->api($row);

        $zone_details = array(
            'name' => $vars['keycdn_name'],
            'originurl' => 'http://'.$vars['keycdn_domain'],
            'type' => 'pull'
        );
        $response = $api->post('zones.json',$zone_details);

        //lets check for errors and return if so
        $result = $this->parseResponse($response, $row);

        if ($this->Input->errors())
            return;


        //get the zone_id
        //$zone_id = $result->data->zone->id;

        return array(
            //approval rows
            array(
                'key' => "keycdn_name",   //domain holder for this cdn
                'value' => $vars['keycdn_name'],
                'encrypted' => 0
            ),
            array(
                'key' => "keycdn_domain",   //domain holder for this cdn
                'value' => $vars['keycdn_domain'],
                'encrypted' => 0
            ),
            array(
                'key' => "keycdn_zone_id",   //zone id
                'value' => '14932',
                'encrypted' => 0
            )
        );
        */
    }

    /**
     * Edits the service on the remote server. Sets Input errors on failure,
     * preventing the service from being edited.
     *
     * @param stdClass $package A stdClass object representing the current package
     * @param stdClass $service A stdClass object representing the current service
     * @param array $vars An array of user supplied info to satisfy the request
     * @param stdClass $parent_package A stdClass object representing the parent service's selected package (if the current service is an addon service)
     * @param stdClass $parent_service A stdClass object representing the parent service of the service being edited (if the current service is an addon service)
     * @return array A numerically indexed array of meta fields to be stored for this service containing:
     * 	- key The key for this meta field
     * 	- value The value for this key
     * 	- encrypted Whether or not this field should be encrypted (default 0, not encrypted)
     * @see Module::getModule()
     * @see Module::getModuleRow()
     */
    public function editService($package, $service, array $vars=array(), $parent_package=null, $parent_service=null) {
        return null;
    }







    /**
     * Cancels the service on the remote server. Sets Input errors on failure,
     * preventing the service from being canceled.
     *
     * @param stdClass $package A stdClass object representing the current package
     * @param stdClass $service A stdClass object representing the current service
     * @param stdClass $parent_package A stdClass object representing the parent service's selected package (if the current service is an addon service)
     * @param stdClass $parent_service A stdClass object representing the parent service of the service being canceled (if the current service is an addon service)
     * @return mixed null to maintain the existing meta fields or a numerically indexed array of meta fields to be stored for this service containing:
     * 	- key The key for this meta field
     * 	- value The value for this key
     * 	- encrypted Whether or not this field should be encrypted (default 0, not encrypted)
     * @see Module::getModule()
     * @see Module::getModuleRow()
     */
    public function cancelService($package, $service, $parent_package=null, $parent_service=null) {
        return null;
    }

    /**
     * Suspends the service on the remote server. Sets Input errors on failure,
     * preventing the service from being suspended.
     *
     * @param stdClass $package A stdClass object representing the current package
     * @param stdClass $service A stdClass object representing the current service
     * @param stdClass $parent_package A stdClass object representing the parent service's selected package (if the current service is an addon service)
     * @param stdClass $parent_service A stdClass object representing the parent service of the service being suspended (if the current service is an addon service)
     * @return mixed null to maintain the existing meta fields or a numerically indexed array of meta fields to be stored for this service containing:
     * 	- key The key for this meta field
     * 	- value The value for this key
     * 	- encrypted Whether or not this field should be encrypted (default 0, not encrypted)
     * @see Module::getModule()
     * @see Module::getModuleRow()
     */
    public function suspendService($package, $service, $parent_package=null, $parent_service=null) {
        return null;
    }

    /**
     * Unsuspends the service on the remote server. Sets Input errors on failure,
     * preventing the service from being unsuspended.
     *
     * @param stdClass $package A stdClass object representing the current package
     * @param stdClass $service A stdClass object representing the current service
     * @param stdClass $parent_package A stdClass object representing the parent service's selected package (if the current service is an addon service)
     * @param stdClass $parent_service A stdClass object representing the parent service of the service being unsuspended (if the current service is an addon service)
     * @return mixed null to maintain the existing meta fields or a numerically indexed array of meta fields to be stored for this service containing:
     * 	- key The key for this meta field
     * 	- value The value for this key
     * 	- encrypted Whether or not this field should be encrypted (default 0, not encrypted)
     * @see Module::getModule()
     * @see Module::getModuleRow()
     */
    public function unsuspendService($package, $service, $parent_package=null, $parent_service=null) {
        return null;
    }

    /**
     * Allows the module to perform an action when the service is ready to renew.
     * Sets Input errors on failure, preventing the service from renewing.
     *
     * @param stdClass $package A stdClass object representing the current package
     * @param stdClass $service A stdClass object representing the current service
     * @param stdClass $parent_package A stdClass object representing the parent service's selected package (if the current service is an addon service)
     * @param stdClass $parent_service A stdClass object representing the parent service of the service being renewed (if the current service is an addon service)
     * @return mixed null to maintain the existing meta fields or a numerically indexed array of meta fields to be stored for this service containing:
     * 	- key The key for this meta field
     * 	- value The value for this key
     * 	- encrypted Whether or not this field should be encrypted (default 0, not encrypted)
     * @see Module::getModule()
     * @see Module::getModuleRow()
     */
    public function renewService($package, $service, $parent_package=null, $parent_service=null) {
        return null;
    }

    /**
     * Updates the package for the service on the remote server. Sets Input
     * errors on failure, preventing the service's package from being changed.
     *
     * @param stdClass $package_from A stdClass object representing the current package
     * @param stdClass $package_to A stdClass object representing the new package
     * @param stdClass $service A stdClass object representing the current service
     * @param stdClass $parent_package A stdClass object representing the parent service's selected package (if the current service is an addon service)
     * @param stdClass $parent_service A stdClass object representing the parent service of the service being changed (if the current service is an addon service)
     * @return mixed null to maintain the existing meta fields or a numerically indexed array of meta fields to be stored for this service containing:
     * 	- key The key for this meta field
     * 	- value The value for this key
     * 	- encrypted Whether or not this field should be encrypted (default 0, not encrypted)
     * @see Module::getModule()
     * @see Module::getModuleRow()
     */
    public function changeServicePackage($package_from, $package_to, $service, $parent_package=null, $parent_service=null) {
        return null;
    }

    /**
     * Validates input data when attempting to add a package, returns the meta
     * data to save when adding a package. Performs any action required to add
     * the package on the remote server. Sets Input errors on failure,
     * preventing the package from being added.
     *
     * @param array An array of key/value pairs used to add the package
     * @return array A numerically indexed array of meta fields to be stored for this package containing:
     * 	- key The key for this meta field
     * 	- value The value for this key
     * 	- encrypted Whether or not this field should be encrypted (default 0, not encrypted)
     * @see Module::getModule()
     * @see Module::getModuleRow()
     */
    public function addPackage(array $vars=null) {

        $meta = array();
        if (isset($vars['meta']) && is_array($vars['meta'])) {
            // Return all package meta fields
            foreach ($vars['meta'] as $key => $value) {
                $meta[] = array(
                    'key' => $key,
                    'value' => $value,
                    'encrypted' => 0
                );
            }
        }

        return $meta;
    }

    /**
     * Validates input data when attempting to edit a package, returns the meta
     * data to save when editing a package. Performs any action required to edit
     * the package on the remote server. Sets Input errors on failure,
     * preventing the package from being edited.
     *
     * @param stdClass $package A stdClass object representing the selected package
     * @param array An array of key/value pairs used to edit the package
     * @return array A numerically indexed array of meta fields to be stored for this package containing:
     * 	- key The key for this meta field
     * 	- value The value for this key
     * 	- encrypted Whether or not this field should be encrypted (default 0, not encrypted)
     * @see Module::getModule()
     * @see Module::getModuleRow()
     */
    public function editPackage($package, array $vars=null) {

        $meta = array();
        if (isset($vars['meta']) && is_array($vars['meta'])) {
            // Return all package meta fields
            foreach ($vars['meta'] as $key => $value) {
                $meta[] = array(
                    'key' => $key,
                    'value' => $value,
                    'encrypted' => 0
                );
            }
        }

        return $meta;
    }

    /**
     * Deletes the package on the remote server. Sets Input errors on failure,
     * preventing the package from being deleted.
     *
     * @param stdClass $package A stdClass object representing the selected package
     * @see Module::getModule()
     * @see Module::getModuleRow()
     */
    public function deletePackage($package) {

    }

    /**
     * Returns the rendered view of the manage module page
     *
     * @param mixed $module A stdClass object representing the module and its rows
     * @param array $vars An array of post data submitted to or on the manage module page (used to repopulate fields after an error)
     * @return string HTML content containing information to display when viewing the manager module page
     */
    public function manageModule($module, array &$vars) {
        // Load the view into this object, so helpers can be automatically added to the view
        $this->view = new View("manage", "default");
        $this->view->base_uri = $this->base_uri;
        $this->view->setDefaultView("components" . DS . "modules" . DS . "keycdn" . DS);

        // Load the helpers required for this view
        Loader::loadHelpers($this, array("Form", "Html", "Widget"));

        $this->view->set("module", $module);

        return $this->view->fetch();
    }

    /**
     * Returns the rendered view of the add module row page
     *
     * @param array $vars An array of post data submitted to or on the add module row page (used to repopulate fields after an error)
     * @return string HTML content containing information to display when viewing the add module row page
     */
    public function manageAddRow(array &$vars) {
        // Load the view into this object, so helpers can be automatically added to the view
        $this->view = new View("add_row", "default");
        $this->view->base_uri = $this->base_uri;
        $this->view->setDefaultView("components" . DS . "modules" . DS . "keycdn" . DS);

        // Load the helpers required for this view
        Loader::loadHelpers($this, array("Form", "Html", "Widget"));


        $this->view->set("vars", (object)$vars);
        return $this->view->fetch();
    }

    /**
     * Returns the rendered view of the edit module row page
     *
     * @param stdClass $module_row The stdClass representation of the existing module row
     * @param array $vars An array of post data submitted to or on the edit module row page (used to repopulate fields after an error)
     * @return string HTML content containing information to display when viewing the edit module row page
     */
    public function manageEditRow($module_row, array &$vars) {
        // Load the view into this object, so helpers can be automatically added to the view
        $this->view = new View("edit_row", "default");
        $this->view->base_uri = $this->base_uri;
        $this->view->setDefaultView("components" . DS . "modules" . DS . "keycdn" . DS);

        // Load the helpers required for this view
        Loader::loadHelpers($this, array("Form", "Html", "Widget"));

        if (empty($vars))
            $vars = $module_row->meta;


        $this->view->set("vars", (object)$vars);
        return $this->view->fetch();
    }

    /**
     * Adds the module row on the remote server. Sets Input errors on failure,
     * preventing the row from being added.
     *
     * @param array $vars An array of module info to add
     * @return array A numerically indexed array of meta fields for the module row containing:
     * 	- key The key for this meta field
     * 	- value The value for this key
     * 	- encrypted Whether or not this field should be encrypted (default 0, not encrypted)
     */
    public function addModuleRow(array &$vars) {
        $meta_fields = array("api_key","account_name");
        $encrypted_fields = array("api_key");

        //set rules
        //$this->Input->setRules($this->getRowRules($vars));
        $this->Input->setRules($this->getRowRules($vars));
        // Validate module row
        if ($this->Input->validates($vars)) {
            // Build the meta data for this row
            $meta = array();
            foreach ($vars as $key => $value) {

                if (in_array($key, $meta_fields)) {
                    $meta[] = array(
                        'key' => $key,
                        'value' => $value,
                        'encrypted' => in_array($key, $encrypted_fields) ? 1 : 0
                    );
                }
            }

            return $meta;
        }
    }
    /**
     * Retrieves a list of rules for validating adding/editing a module row
     *
     * @param array $vars A list of input vars
     * @return array A list of rules
     */
    private function getRowRules(array &$vars)
    {
        return array(
            'api_key' => array(
                'empty' => array(
                    'rule' => "isEmpty",
                    'negate' => true,
                    'message' => Language::_("keycdn.!error.api_key.empty", true)
                ),
                'valid' => array(
                    'rule' => array(array($this, "validateConnection"), $vars),
                    'message' => Language::_("keycdn.!error.api_key.invalid", true)
                )
            ),
            'account_name' => array(
                'empty' => array(
                'rule' => "isEmpty",
                'negate' => true,
                'message' => Language::_("keycdn.!error.account_name.empty", true)
                )
            )
        );
    }

    /**
     * Validates whether or not the connection details are valid by attempting to fetch
     * the number of accounts that currently reside on the server
     *
     * @param string $api_username The reseller API username
     * @param array $vars A list of other module row fields including:
     * 	- api_password The reseller password
     * 	- sandbox "true" or "false" as to whether sandbox is enabled
     * @return boolean True if the connection is valid, false otherwise
     */
    public function validateConnection($api_username, $vars) {

        try {
            $api_key = (isset($vars['api_key']) ? $vars['api_key'] : "");

            $module_row = (object)array('meta' => (object)$vars);

            $api = $this->api($module_row);
            //we are just going to pull the zones to check
            $zones = $api->get('zones.json');

            if (!$this->Input->errors())
                return true;

            // Remove the errors set
            $this->Input->setErrors(array());
        } catch (Exception $e) {
            // Trap any errors encountered, could not validate connection
        }
        return false;
    }
    /**
     * Edits the module row on the remote server. Sets Input errors on failure,
     * preventing the row from being updated.
     *
     * @param stdClass $module_row The stdClass representation of the existing module row
     * @param array $vars An array of module info to update
     * @return array A numerically indexed array of meta fields for the module row containing:
     * 	- key The key for this meta field
     * 	- value The value for this key
     * 	- encrypted Whether or not this field should be encrypted (default 0, not encrypted)
     */
    public function editModuleRow($module_row, array &$vars) {
        // Same as adding
        return $this->addModuleRow($vars);
    }

    /**
     * Deletes the module row on the remote server. Sets Input errors on failure,
     * preventing the row from being deleted.
     *
     * @param stdClass $module_row The stdClass representation of the existing module row
     */
    public function deleteModuleRow($module_row) {

    }

    /**
     * Returns an array of available service delegation order methods. The module
     * will determine how each method is defined. For example, the method "first"
     * may be implemented such that it returns the module row with the least number
     * of services assigned to it.
     *
     * @return array An array of order methods in key/value pairs where the key is the type to be stored for the group and value is the name for that option
     * @see Module::selectModuleRow()
     */
    public function getGroupOrderOptions() {

    }

    /**
     * Determines which module row should be attempted when a service is provisioned
     * for the given group based upon the order method set for that group.
     *
     * @return int The module row ID to attempt to add the service with
     * @see Module::getGroupOrderOptions()
     */
    public function selectModuleRow($module_group_id) {

    }

    /**
     * Returns all fields used when adding/editing a package, including any
     * javascript to execute when the page is rendered with these fields.
     *
     * @param $vars stdClass A stdClass object representing a set of post fields
     * @return ModuleFields A ModuleFields object, containg the fields to render as well as any additional HTML markup to include
     */
    public function getPackageFields($vars=null) {
        Loader::loadHelpers($this, array("Form", "Html"));

        $fields = new ModuleFields();

        $row = null;
        if (isset($vars->module_group) && $vars->module_group == "") {
            if (isset($vars->module_row) && $vars->module_row > 0) {
                $row = $this->getModuleRow($vars->module_row);
            } else {
                $rows = $this->getModuleRows();
                if (isset($rows[0]))
                    $row = $rows[0];
                unset($rows);
            }
        } else {
            // Fetch the 1st server from the list of servers in the selected group
            $rows = $this->getModuleRows($vars->module_group);

            if (isset($rows[0]))
                $row = $rows[0];
            unset($rows);
        }


        return $fields;
    }

    /**
     * Returns an array of key values for fields stored for a module, package,
     * and service under this module, used to substitute those keys with their
     * actual module, package, or service meta values in related emails.
     *
     * @return array A multi-dimensional array of key/value pairs where each key is one of 'module', 'package', or 'service' and each value is a numerically indexed array of key values that match meta fields under that category.
     * @see Modules::addModuleRow()
     * @see Modules::editModuleRow()
     * @see Modules::addPackage()
     * @see Modules::editPackage()
     * @see Modules::addService()
     * @see Modules::editService()
     */
    public function getEmailTags() {
        return array(
            'module' => array("keycdn_domain"),
            'package' => array("keycdn_domain"),
            'service' => array("keycdn_domain")
        );

    }

    /**
     * Returns all fields to display to an admin attempting to add a service with the module
     *
     * @param stdClass $package A stdClass object representing the selected package
     * @param $vars stdClass A stdClass object representing a set of post fields
     * @return ModuleFields A ModuleFields object, containg the fields to render as well as any additional HTML markup to include
     */
    public function getAdminAddFields($package, $vars=null) {
        return new ModuleFields();
    }

    /**
     * Returns all fields to display to a client attempting to add a service with the module
     *
     * @param stdClass $package A stdClass object representing the selected package
     * @param $vars stdClass A stdClass object representing a set of post fields
     * @return ModuleFields A ModuleFields object, containg the fields to render as well as any additional HTML markup to include
     */
    public function getClientAddFields($package, $vars=null) {
        //for now we are manually adding this packahe in so will not be requesting any client fields
        return new ModuleFields();
        Loader::loadHelpers($this, array("Form", "Html"));
        //We are just going to get domain name we want CDN service for
        $fields = new ModuleFields();
        /*
        $fields->setHtml("
			<script type=\"text/javascript\">
                $(document).ready(function() {
                    $('#keycdn_domain').change(function() {
						var form = $(this).closest('form');
						$(form).append('<input type=\"hidden\" name=\"refresh_fields\" value=\"true\">');
						$(form).submit();
					});
                });
			</script>
		");*/
        //create client form
        //keycdn_name
        $keycdn_name = $fields->label(Language::_("keycdn.service_field.name", true), "keycdn_name");
        $keycdn_name->attach($fields->fieldText("keycdn_name", $this->Html->ifSet($vars->keycdn_name), array('id' => "keycdn_name")));
        $fields->setField($keycdn_name);
        //domain name
        $keycdn_domain = $fields->label(Language::_("keycdn.service_field.domain", true), "keycdn_domain");
        $keycdn_domain->attach($fields->fieldText("keycdn_domain", $this->Html->ifSet($vars->keycdn_domain), array('id' => "keycdn_domain")));
        $fields->setField($keycdn_domain);
        //unset
        unset($keycdn_domain);
        unset($keycdn_name);

        return $fields;
    }

    /**
     * Returns all fields to display to an admin attempting to edit a service with the module
     *
     * @param stdClass $package A stdClass object representing the selected package
     * @param $vars stdClass A stdClass object representing a set of post fields
     * @return ModuleFields A ModuleFields object, containg the fields to render as well as any additional HTML markup to include
     */
    public function getAdminEditFields($package, $vars=null) {
        return new ModuleFields();
    }

    /**
     * Fetches the HTML content to display when viewing the service info in the
     * admin interface.
     *
     * @param stdClass $service A stdClass object representing the service
     * @param stdClass $package A stdClass object representing the service's package
     * @return string HTML content containing information to display when viewing the service info
     */
    public function getAdminServiceInfo($service, $package) {
        return "";
    }

    /**
     * Fetches the HTML content to display when viewing the service info in the
     * client interface.
     *
     * @param stdClass $service A stdClass object representing the service
     * @param stdClass $package A stdClass object representing the service's package
     * @return string HTML content containing information to display when viewing the service info
     */
    public function getClientServiceInfo($service, $package) {
        return "";
    }

    /**
     * Returns all tabs to display to an admin when managing a service whose
     * package uses this module
     *
     * @param stdClass $package A stdClass object representing the selected package
     * @return array An array of tabs in the format of method => title. Example: array('methodName' => "Title", 'methodName2' => "Title2")
     */
    public function getAdminTabs($package)
    {
        return array(
            'tabAdminSettings' => Language::_("keycdn.tab.admin.settings", true)
        );
    }
    /**
     * Admin Settings tab
     *
     * @param stdClass $package A stdClass object representing the current package
     * @param stdClass $service A stdClass object representing the current service
     * @param array $get Any GET parameters
     * @param array $post Any POST parameters
     * @param array $files Any FILES parameters
     * @return string The string representing the contents of this tab
     */
    public function tabAdminSettings($package, $service, array $getRequest = null, array $postRequest = null, array $files = null)
    {
        $row = $this->getModuleRow($package->module_row);


        $this->view = new View("tab_admin_manage", "default");

        $this->view->base_uri = $this->base_uri;
        // Load the helpers required for this view
        Loader::loadHelpers($this, array("Form", "Html"));

        //Get the service fields
        /*stdClass Object ( [keycdn_domain] => screepts.com [keycdn_name] => screepts [keycdn_zone_id] => 14932 )*/
        $service_fields = $this->serviceFieldsToObject($service->fields);


        //pass requirements to view
        $this->view->set("client", $service_fields);
        $this->view->set("view", $this->view->view);
        $this->view->setDefaultView("components" . DS . "modules" . DS . "keycdn" . DS);

        return $this->view->fetch();
    }
    /**
     * Returns all tabs to display to a client when managing a service whose
     * package uses this module
     *
     * @param stdClass $package A stdClass object representing the selected package
     * @return array An array of tabs in the format of method => title, or method => array where array contains:
     * 	- name (required) The name of the link
     * 	- icon (optional) use to display a custom icon
     * 	- href (optional) use to link to a different URL
     * 		Example: array('methodName' => "Title", 'methodName2' => "Title2")
     * 		array('methodName' => array('name' => "Title", 'icon' => "icon"))
     */
    public function getClientTabs($package) {
        return array(
            'tabClientManage' => array('name' => Language::_("keycdn.tab.client.manage", true), 'icon' => "fa fa-chain-broken"),
            'tabClientStats' => array('name' => Language::_("keycdn.tab.client.stats", true), 'icon' => "fa fa-file-text-o"),
        );
    }

    /**
     * Client Manage tab
     *
     * @param stdClass $package A stdClass object representing the current package
     * @param stdClass $service A stdClass object representing the current service
     * @param array $get Any GET parameters
     * @param array $post Any POST parameters
     * @param array $files Any FILES parameters
     * @return string The string representing the contents of this tab
     */
    public function tabClientManage($package, $service, array $getRequest = null, array $postRequest = null, array $files = null)
    {

        $row = $this->getModuleRow($package->module_row);

        if (isset($postRequest["keycdn_purge"]) && isset($postRequest["keycdn_purge_url"])) {
            print_r($postRequest);exit;
        }
        $this->view = new View("tab_client_manage", "default");

        $this->view->base_uri = $this->base_uri;
        // Load the helpers required for this view
        Loader::loadHelpers($this, array("Form", "Html"));

        //Get the service fields
        /*stdClass Object ( [keycdn_domain] => screepts.com [keycdn_name] => screepts [keycdn_zone_id] => 14932 )*/
        $service_fields = $this->serviceFieldsToObject($service->fields);


        //pass requirements to view
        $this->view->set("service_id", $service->id);
        //trim the cdn domain and add slash
        $this->view->set("purge_url",   rtrim($service_fields->keycdn_domain, '/') . '/');
        $this->view->set("view", $this->view->view);
        $this->view->setDefaultView("components" . DS . "modules" . DS . "keycdn" . DS);

        return $this->view->fetch();
    }
    /**
     * Client Stats tab
     *
     * @param stdClass $package A stdClass object representing the current package
     * @param stdClass $service A stdClass object representing the current service
     * @param array $get Any GET parameters
     * @param array $post Any POST parameters
     * @param array $files Any FILES parameters
     * @return string The string representing the contents of this tab
     */
    public function tabClientStats($package, $service, array $getRequest = null, array $postRequest = null, array $files = null)
    {
        $this->view->base_uri = $this->base_uri;
        // Load the helpers required for this view
        Loader::loadHelpers($this, array("Form", "Html"));
        //Get the service fields
        $service_fields = $this->serviceFieldsToObject($service->fields);
        $row = $this->getModuleRow($package->module_row);
    }
    private function api($module_row = false)
    {
        //singleton
        if ($this->_api == false) {

            //if module_row was not passed will try retrieve
            if ($module_row == false || !isset($module_row))
                $module_row = $this->getModuleRow();

            if (!isset($module_row)) {
                die ("failed to load api (module row issue)");
            }
            //load our api
            Loader::load(dirname(__FILE__) . DS . "lib" . DS . "KeyCDNApi.php");

            $this->_api = new KeyCDNApi($module_row->meta->api_key);


        }


        return $this->_api;
    }

    /**
     * Parses the response from API into an stdClass object
     *
     * @param mixed $response The response from the API
     * @param stdClass $module_row A stdClass object representing a single reseller (optional, required when Module::getModuleRow() is unavailable)
     * @param boolean $ignore_error Ignores any response error and returns the response anyway; useful when a response is expected to fail (e.g. check client exists) (optional, default false)
     * @return stdClass A stdClass object representing the response, void if the response was an error
     */
    public function parseResponse($response, $module_row = null, $ignore_error = false)
    {
        Loader::loadHelpers($this, array("Html"));

        // Set the module row
        if (!$module_row)
            $module_row = $this->getModuleRow();

        $success = true;

        if (empty($response) || !empty($response['error']) || (!empty($response['status'])) && $response['status'] == "error") {
            $success = false;
            $error = (isset($response['description'])) ? $response['description'] : Language::_("keycdn.!error.api.internal", true);


            if (!$ignore_error)
                $this->Input->setErrors(
                    array('api' =>
                        array('internal' =>
                            $error
                        )
                    )
                );
            //$this->Input->setErrors(array('errors' => $error));


            //$this->Input->setErrors(array('api' => array('internal' => $error)));

        }

        $this->log($module_row->meta->account_name, serialize($response), "output", $success);

        if (!$success && !$ignore_error)
            return;

        return $response;
    }








}