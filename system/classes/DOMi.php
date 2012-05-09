<?php

/**
 * Improved DOM object - a merger of DOMDocument, DOMXpath and XSLTProcessor, with extended functionality for converting PHP data types into an XML structure
 *
 * merger of Steve Phillips' AttachToXml and Chris Webb's XmlBuilder. AttachToXml is an extension of Steve Howe's ConvertObjectList.
 *
 * @author Steve Phillips <visual77@gmail.com>
 * @author Chris Webb <life.42@gmail.com>
 * @author Phillip Brown (like the color) <devphillipbrown@gmail.com>
 * @author Steve Howe
 * @version 1.0.0 
 */

class DOMi
    {
        var $ListSuffix = "-list";              ///< @var string the text that will be attached to the end of a prefix when it applies to an array
        var $Xslt;                              ///< @var XSLTProcessor an XSLTProcessor built into DOMi 
        var $Dom;                               ///< @var DOMDocument a DOMDocument built into DOMi 
        var $Xpath;                             ///< @var DOMXpath a DOMXpath built into DOMi 
        var $ExceptionPrefix = false;           ///< @var string prefixes to be processed without reconfiguration
        var $Namespace = array();               ///< @var array a list of namespaces added to the document
        var $MainNode;                          ///< @var domnode the root node for the dom document
        protected $DisabledXPaths = array();    ///< @var list of XPaths to check for right before rendering
        protected $DisabledPrefixes = array();  ///< @var list of 'prefixes' to ignore when passed to AttachToXml method
        
        /// @var const regular expression matching a namespace prefix
        const REGEX_NS_PREFIX = '/^[a-zA-Z]+$/';
        /// @var const regular expression matching a url
        const REGEX_URL    = '/^(?:https?:\\/\\/)?([a-zA-Z0-9]\\w*\\.)*(?:[a-zA-Z0-9]\\w*)(?:\\.[a-zA-Z]+)+(?:\\/.*)?$/';
        /// @var const regular expression matching a valid node name
        const REGEX_PREFIX = '/^[a-zA-Z][-a-zA-Z0-9_.]*$/';
        
        const RENDER_VIEW = 'render_view';
        const RENDER_HTML = 'render_html';
        const RENDER_XML  = 'render_xml';
        
        /**
         * Description: set up the DOMDocument, XSLTProcessor and DOMXpath for use by the later functions
         *
         * @param mixed the root node, either as the name of the node to be created, 
         * the location of the document to import or the domdocument to use
         * @retval DOMi
         */
        public function __construct($MainNodeName)
            {
                // if the user provided a dom document, use that as the central dom and manipulate that
                if ($MainNodeName instanceof DOMDocument)
                    {
                        $this->LoadDomDoc($MainNodeName);
                    }
                // if the user provided a string... 
                else if (is_string($MainNodeName))
                    {
                        // ...and that string matches an existing file, load the xml from that file and convert it to dom
                        if (file_exists($MainNodeName) && $this->IsValidXml($MainNodeName))
                            {
                                $this->LoadDomFromFile($MainNodeName);
                            }
                        // ..and that string does not correspond to an existing file that contains an xml tree
                        else
                            {
                                // build a domelement with the provided string as the node name and attach it as the main element
                                $this->Dom = new DOMDocument('1.0', 'UTF-8');
                                $this->MainNode = $this->AttachToXml(null, $MainNodeName, $this->Dom);
                            }
                    }
                
                $this->Xslt = new XSLTProcessor();
                $this->Xpath = new DOMXpath($this->Dom);
                $this->MainNode =& $this->Xpath->query("/*")->item(0);
            }
        
        /**
         * provide a file location for an xml to be loaded
         *
         * @param string the location of the xml to be loaded
         * @retval void
         * @bug if the provided file isnt a valid xml document, this will crash 
         */
        public function LoadDomFromFile($File)
            {
                $this->Dom = new DOMDocument();
                $this->Dom->load($File);
            }
        
        /**
         * take the provided dom document and store it as the primary dom document
         *
         * @param domdocument object that will be parsed using the DOMi class
         * @retval void
         */
        public function LoadDomDoc(&$Doc)
            {
                // take the provided document and store it as the primary dom document
                $this->Dom = $Doc;
            }
        
        /**
         * invoke methods from DOMDocument, DOMXpath and XSLTProcessor transparently
         *
         * this magic method checks the three main objects (DOMDocument, DOMXpath, XSLTProcessor) to see if the requested
         * method exists within those objects, and if so, build up a PHP call and eval() that call. while eval is typically
         * very vulnerable, this function limits what can be sent to eval() to a very narrow selection, thus preventiny any
         * exploits. only the name of the method can be controlled by the user, and that is limited by the PHP parser.
         *
         * @param string the name of the requested method
         * @param array the parameters that are being sent to the requested method
         * @retval mixed the returned value of the requested function
         */
        public function __call($Method, $Parameters)
            {
                // set up a string that lists each passed parameter in a format that can be sent to the method
                $ParamArray = array();
                $ParamCount = count($Parameters);
                for($i = 0; $i < $ParamCount; ++$i)
                    {
                        $ParamArray[] = '$Parameters['.$i.']';
                    }
                $ParamString = implode(', ', $ParamArray);
                
                // find out which object is being requested by figuring out which one contains the requested method
                $Obj = false;
                if(method_exists($this->Dom, $Method))
                    {
                        $Obj = 'Dom';
                    }
                elseif(method_exists($this->Xpath, $Method))
                    {
                        $Obj = 'Xpath';
                    }
                elseif(method_exists($this->Xslt, $Method))
                    {
                        $Obj = 'Xslt';
                    }
                
                if($Obj)
                    {
                        // pass the created string to eval for execution
                        $EvalString = 'return $this->'.$Obj.'->$Method('.$ParamString.');';
                        return eval($EvalString);
                    }
                else
                    {
                        return false;
                    }
            }
        
        /**
         * magic method to transparently provide access to the three main object's member properties
         *
         * @param string the property that was requested
         * @retval mixed the property that was located
         */
        public function __get($Property)
            {
                $Request = null;
                
                if(isset($this->Dom->$Property))
                    {
                        $Request = $this->Dom->$Property;
                    }
                if(isset($this->Xpath->$Property))
                    {
                        $Request = $this->Xpath->$Property;
                    }
                if(isset($this->Xslt->$Property))
                    {
                        $Request = $this->Xslt->$Property;
                    }
                
                return $Request;
            }
        
        /**
         * Convert a wide variety of php data types into xml data and attach it to the dom tree. Multidimensonal arrays
         * are supported and will create a nested tree. If an array is being passed, the name of the nodes will be adjusted
         * based on the names of the keys. An all numeric list will have the list suffix attached to the parent node (default
         * is '-list') and each child node will be named as the prefix. An all string list will name the parent node 
         * as the prefix, and the child nodes will be named according to their keys. A mixed string/numeric list will 
         * have the parent named with the list suffix, the numeric keys named as the prefix, and the string keys named after 
         * the key. If the prefix is 'attributes', all of the elements of the array will be created as attributes of the parent 
         * node. If the prefix is 'value' and an array with one element is passed, the data will be set as the node value, and this
         * is mostly to be used when assigning attributes.
         *
         * Example of list suffix with a numeric key array
         *
         * <pre>
         * $Array[0] = 'zero';
         * $Array[1] = 'one';
         * $Array[2] = 'two';
         * $Domi = new DOMi('root');
         * $Domi->attachToXml($Array, 'number');
         * $Domi->Render(DOMi::RENDER_XML);
         * 
         * <? xml version="1.0" encoding="UTF-8" ?>
         * <root>
         *   <number-list>
         *     <number>zero</number>
         *     <number>one</number>
         *     <number>two</number>
         *   </number-list>
         * </root>
         * </pre>
         *
         * Example of list suffix with string key array
         *
         * <pre>
         * $Array['first'] = 'alpha';
         * $Array['second'] = 'beta';
         * $Array['third'] = 'gamma';
         * $Domi = new DOMi('root');
         * $Domi->attachToXml($Array, 'letters');
         * $Domi->Render(DOMi::RENDER_XML);
         * 
         * <? xml version="1.0" encoding="UTF-8" ?>
         * <root>
         *   <letters>
         *     <first>alpha</first>
         *     <second>beta</second>
         *     <third>gamma</third>
         *   </letters>
         * </root>
         * </pre>
         *
         * Example of list suffix with mixed key array
         *
         * <pre>
         * $Array['start'] = 'solid';
         * $Array[] = 'liquid';
         * $Array[] = 'solidus';
         * $Array['finish'] = 'naked';
         * $Domi = new DOMi('root');
         * $Domi->attachToXml($Array, 'mixed');
         * $Domi->Render(DOMi::RENDER_XML);
         * 
         * <? xml version="1.0" encoding="UTF-8" ?>
         * <root>
         *   <mixed-list>
         *     <start>solid</start>
         *     <mixed>liquid</mixed>
         *     <mixed>solidus</mixed>
         *     <finish>named</finish>
         *   </mixed-list>
         * </root>
         * </pre>
         *
         * Example of a multidimensional array
         *
         * <pre>
         * $Array[0]['name'] = 'Lloyd';
         * $Array[0]['class'] = 'fighter';
         * $Array[0]['moves'][] = 'Beast';
         * $Array[0]['moves'][] = 'Demon Fang';
         * $Array[1]['name'] = 'Genis';
         * $Array[1]['class'] = 'mage';
         * $Array[1]['moves'][] = 'Stalagmite';
         * $Array[1]['moves'][] = 'Fireball';
         * $Array[2]['name'] = 'Raine';
         * $Array[2]['class'] = 'healer';
         * $Array[2]['moves'][] = 'First Aid';
         * $Array[2]['moves'][] = 'Resurrection';
         * $Domi = new DOMi('root');
         * $Domi->attachToXml($Array, 'character');
         * $Domi->Render(DOMi::RENDER_XML);
         *
         * <? xml version="1.0" encoding="UTF-8" ?>
         * <root>
         *   <character-list>
         *     <character>
         *       <name>Lloyd</name>
         *       <class>fighter</class>
         *       <move-list>
         *         <move>Beast</move>
         *         <move>Demon Fang</move>
         *       </move-list>
         *     </character>
         *     <character>
         *       <name>Genis</name>
         *       <class>mage</class>
         *       <move-list>
         *         <move>Stalagmite</move>
         *         <move>Fireball</move>
         *       </move-list>
         *     </character>
         *     <character>
         *       <name>Raine</name>
         *       <class>healer</class>
         *       <move-list>
         *         <move>First Aid</move>
         *         <move>Resurrection</move>
         *       </move-list>
         *     </character>
         *   </character-list>
         * </root>
         * </pre>
         *
         * Example of adding attributes and values
         *
         * <pre>
         * $Array[0]['value'] = 'slow movement and lots of health';
         * $Array[0]['attributes']['class'] = 'Heavy';
         * $Array[0]['attributes']['primary'] = 'Gatling gun';
         * $Array[0]['attributes']['melee'] = 'fists';
         * $Array[1]['value'] = 'very fast with low health';
         * $Array[1]['attributes']['class'] = 'Scout';
         * $Array[1]['attributes']['primary'] = 'Shotgun';
         * $Array[1]['attributes']['melee'] = 'baseball bat';
         * $Array[2]['value'] = 'support healing with low health';
         * $Array[2]['attributes']['class'] = 'Medic';
         * $Array[2]['attributes']['primary'] = 'Healing gun';
         * $Array[2]['attributes']['melee'] = 'bonesaw';
         * $Domi = new DOMi('root');
         * $Domi->attachToXml($Array, 'class');
         * $Domi->Render(DOMi::RENDER_XML);
         *
         * <? xml version="1.0" encoding="UTF-8" ?>
         * <root>
         *   <class-list>
         *     <character class="Heavy" primary="Gatling gun" melee="fists">slow movement and lots of health</character>
         *     <character class="Scout" primary="Shotgun" melee="baseball bat">very fast with low health</character>
         *    <character class="Medic" primary="Healing gun" melee="bonesaw">support healing with low health</character>
         *   </class-list>
         * </root>
         * </pre>
         *
         * @param mixed the data that is being passed to populate the created node
         * @param string the name of the node you are going to create and populate with data
         * @param domnode the node that the newly created node will be attached onto
         * @retval domnode the node that was created to house the new data
         */
        public function AttachToXml($Data, $Prefix, &$ParentNode = false, $Siblings=false, $ForceList=false)
            {
                $Prefix = str_replace(' ', '-', $Prefix);
                // check if this is a disabled prefix. if so ignore this one and return null
                if (count($this->DisabledPrefixes) > 0 && array_search(strtolower($Prefix), $this->DisabledPrefixes) !== false && strtolower($Prefix) != 'attributes' && strtolower($Prefix) != 'values')
                    {
                        return null;
                    }
                
                // if no parent node was provided, attach to the root node
                if(!$ParentNode)
                    {
                        $ParentNode = $this->MainNode;
                    }
                
                // if a DOMi object is passed over, just select the DOMDocument inside of it
                if($Data INSTANCEOF DOMi)
                    {
                        $Data = $Data->Dom;
                    }
                
                // make sure the prefix is valid
                /// @todo incorporate scanning for acceptable namespaces, as well as valid prefixes
                if($this->ValidPrefix($Prefix) === false)
                    {
                        trigger_error("<br/>\nunable to build xml tree due to invalid prefix '$Prefix'<br/>\n", E_USER_WARNING);
                        
                        return false;
                    }
                else
                    {
                        // $NewNode =& $this->GetNewNode($Data, $Prefix, $ParentNode);
                        $TotalChildren = is_array($Data) ? count($Data) : 1;
                        $SimilarNamedChildren = $this->CountSimilarNames($Prefix, $Data);
                        $NewNode =& $this->GetNewNode($Prefix, $ParentNode, $Siblings, $TotalChildren, $SimilarNamedChildren, $ForceList);
                    }
                
                // if its a string or a number, just throw together a single node, attach, and exit
                if(is_string($Data) || is_numeric($Data) || is_int($Data))
                    {
                        if(file_exists($Data) && $this->IsValidXml($Data))
                            {
                                $DOMi = new DOMi($Data);
                                $this->AttachToXml($DOMi->Dom, $Prefix, $NewNode);
                            }
                        else
                            {
                                $NewNode->nodeValue = preg_replace('/&(?!(#\\d{1,4};|[a-zA-Z]{1,6};))/', '&#038;', $Data);
                            }
                    }
                // if its an array, build it up and attach
                elseif(is_array($Data) && strtolower($Prefix) != 'attributes')
                    {
                        $DataI = array();
                        $DataA = array();
                        
                        // filter array based on the key. numeric keys and string keys are to be separated
                        foreach ($Data as $Key=>$Value)
                            {
                                if (is_numeric($Key))
                                    {
                                        $DataI[] = $Value;
                                    }
                                else
                                    {
                                        $DataA[$Key] = $Value;
                                    }
                            }
                        
                        // recursively call for each of the numeric keys to attach them to the new top node
                        foreach ($DataI as $Value)
                            {
                                $this->AttachToXml($Value, $Prefix, $NewNode, $this->SiblingsExist($Data));
                            }
                        
                        // go through each of the string keys and either add them as attributes or recursively add them to the top node
                        foreach ($DataA as $Key=>$Value)
                            {
                                $this->AttachToXml($Value, $Key, $NewNode, $this->SiblingsExist($Data));
                            }
                    }
                //if it is attributes, create attributes rather than building a new node
                elseif(is_array($Data) && strtolower($Prefix) == 'attributes')
                    {
                        foreach($Data as $Attr=>$Value)
                            {
                                $NewNode->setAttribute($Attr, (string)preg_replace('/&(?!(#\d{1,4}|[a-zA-Z]{1,6}));/', '&#038;', $Value));
                            }
                    }
                // if its a DOMNode object, import it and attach
                elseif($Data INSTANCEOF DOMDocument)
                    {
                        // if it has multiple root nodes(naughty devs...), load them all
                        for($i = 0; $i < $Data->childNodes->length; ++$i)
                            {
                                $this->AttachToXml($Data->childNodes->item($i), $Prefix, $NewNode, $Data->childNodes->length > 1);
                            }
                    }
                // if its a DOMDocument object, attach all of its child nodes
                elseif($Data INSTANCEOF DOMNode)
                    {
                        // attach the provided node to the main node of the primary dom document
                        $NewNode->appendChild($this->Dom->importNode($Data, true));
                    }
                elseif(is_object($Data))
                    {
                        $Temp = $this->ConvObjToArray($Data);
                        $this->AttachToXml($Temp, $Prefix, $NewNode);
                    }
                
                return $NewNode;
            }
        
        /**
         * Stroke of genius to convert any object into an array with no loss of data so that it can be attached to the XML tree easily.
         * 
         * @param object object to be converted to an array
         * @retval array the array generated by this iteration of the method, derived from the object passed to it
         */
        public function ConvObjToArray($Obj)
            {
                /* if the passed arguement is actually an object the capture the name of the class the object is made from */
                $Classes = false;
                if (is_object($Obj))
                    {
                        $Classes = $this->GetAllAncestors(get_class($Obj));
                    }
                
                /* type cast the object to an array which only casts the top most level of properties to array elements */
                $Return = (array) $Obj;
                
                /* cycle through all of the new elements in the new array and check if any of the elements are of extended 
                 * data types like array or object. */
                foreach ($Return as $Property => $Value)
                    {
                        /* if they are of an extended data type, then pass that element back recursively for processing */
                        if (is_object($Value) || is_array($Value))
                            {
                                $Return[$Property] = $this->ConvObjToArray($Value);
                            }
                        
                        /* correct the element name to remove spaces because DOMi will not support them because XML does not */
                        if (strpos($Property, ' ') !== false)
                            {
                                $Return[str_replace(' ', '-', $Property)] = $Return[$Property];
                                unset($Return[$Property]);
                                $Property = str_replace(' ', '-', $Property);
                            }
                        
                        /* if the current property is of type protected, remove the special syntax indicating so */
                        if ($Classes !== false && $Property{1} == '*')
                            {
                                $Return[substr($Property,3)] = $Return[$Property];
                                unset($Return[$Property]);
                            }
                        /* if the current property is of type private, remove the special syntax indicating so */
                        else if ($Classes !== false)
                            {
                                foreach ($Classes as $Class)
                                    {
                                        if (substr($Property, 1, strlen($Class)) == $Class)
                                            {
                                                $Return[substr($Property,strlen($Class)+2)] = $Return[$Property];
                                                unset($Return[$Property]);
                                            }
                                    }
                            }
                        
                    }
                
                /* return this iteration's resulting array */
                return $Return;
            }
        
        private function GetALlAncestors($CurrentClass)
            {
                $Parents = array($CurrentClass);
                while ($Parents[] = get_parent_class(end($Parents)));
                return array_splice($Parents, 0, count($Parents)-2);
            }
        
        private function SiblingsExist(&$Data)
            {
                $AttrKeys = 0;
                $ValKeys = 0;
                $AttrKeys = array_keys($Data,'attributes');
                $ValKeys = array_keys($Data,'values');
                return count($Data) - count($AttrKeys) - count($ValKeys) > 1 ? true : false;
            }
        
        private function CountSimilarNames(&$Prefix, &$Data)
            {
                if(is_array($Data))
                    {
                        $DataI = array();
                        $DataA = array();
                        foreach ($Data as $Key=>$Value)
                            {
                                if (is_numeric($Key))
                                    {
                                        $DataI[] = $Value;
                                    }
                                else
                                    {
                                        $DataA[$Key] = $Value;
                                    }
                            }
                        
                        $Total = count($DataI);
                        
                        if(count($DataA) > 0)
                            {
                                foreach($DataA as $Key=>$Value)
                                    {
                                        if(trim(strtolower($Key)) == trim(strtolower($Prefix)))
                                            {
                                                $Total++;
                                            }
                                    }
                            }
                        
                        return $Total;
                    }
                return 0;
            }
        
        /**
         * return an applicable node based on the number of children, siblings, prefix and parent node
         *
         * @param string the name of the node that is attempting to be built
         * @param domnode the node that the new data is being placed into
         * @param bool whether this node has siblings
         * @param bool whether the children nodes have similar names to this node, meaning either if they are numerically indexed or have the same string name
         * @param integer the total number of children this node could have (only if this is an array), of which these children can be any type
         * @param bool whether to force all nodes with children to be forced to node name and suffix syntax
         * @retval DOMNode node to which the data in question should be attached
         */
        private function &GetNewNode(&$Prefix, &$ParentNode, $Siblings=false, $TotalChildren=0, $SimilarNamedChildren=0, $ForceList=false)
            {
                /* First Law of DOMi: NEVER collapse siblings. */
                /* if the current node has siblings, then we will not even think about collapsing it. however it if does,
                 * then flag the node as being possible to collapse to the parent node, warrenting further testing for
                 * node name comparison */
                $PossibleCollapse = false;
                if(!$Siblings)
                    {
                        $PossibleCollapse = true;
                    }
                
                /* Second Law of DOMi: A node as a child of a node with the same name must collapse (unless it conflicts
                 * with the First Law. */
                /* if the node is flagged as possibly being collapsable and if the name of the current node matches the name
                 * of the parent node, then mark the node as set to collapse */
                $Collapse = false;
                if(isset($ParentNode->tagName) && $PossibleCollapse && trim(strtolower($ParentNode->tagName)) == trim(strtolower($Prefix)))
                    {
                        $Collapse = true;
                    }
                
                /* Addendum: if the child is named attributes, mark as collapable because the attributes are applied to the parent */
                if (trim(strtolower($Prefix)) == 'attributes' || trim(strtolower($Prefix)) == 'values')
                    {
                        $Collapse = true;
                    }
                
                /* return the lowest node on the tree so far that meet the credentials to have the data in question attach to */
                return $this->LowestUniqueNode($Prefix, $ParentNode, $Collapse, $TotalChildren, $SimilarNamedChildren, $ForceList);
            }
        
        /**
         * Takes the given information of desired prefix name, desired parent node, sibling count, child count, similar named
         * child count, and the flag to force a list, to generate a node for the actual data to be attached to.
         * 
         * @param string the name of the node that is attempting to be built
         * @param domnode the node that the new data is being placed into
         * @param bool whether the node is to be collapsed to the parent node
         * @param bool whether the children nodes have similar names to this node, meaning either if they are numerically indexed or have the same string name
         * @param integer the total number of children this node could have (only if this is an array), of which these children can be any type
         * @param bool whether to force all nodes with children to be forced to node name and suffix syntax
         * @retval DOMNode node to which the data in question should be attached
         */
        private function &LowestUniqueNode(&$Prefix, &$ParentNode, $Collapse, $TotalChildren, $SimilarNamedChildren, $ForceList)
            {
                /* create a possibly temporary sub node of the parent node by default */
                $TopNode = $this->Dom->createElement($Prefix);
                $ParentNode->appendChild($TopNode);
                
                /* if the node is set to be collapsed, then remove the temporary node created above, and set the TopNode 
                 * as the parent, so that it may be used in place of the current node. */
                if($Collapse)
                    {
                        $ParentNode->removeChild($TopNode);
                        $TopNode = $ParentNode;
                    }
                
                /* regardless if the current node is actually the parent node or if it is the temporary node created above,
                 * check howmany children the node has and if any of those children (if more than one child exists) have the
                 * same name as the current node. if there is more than one and atleast one of them share the same name,
                 * then recreate the node, copy over all information, and put the node name set to the same name with 
                 * addition of the ListSuffix. This will also happen if the ForceList flag is set, regardless of child count */
                if(($TotalChildren>1 && $SimilarNamedChildren>0) || $ForceList)
                    {
                        $UpperNode = $TopNode->parentNode;
                        $LowerNode = $this->Dom->createElement($Prefix.$this->ListSuffix);
                        $TopChildren = $TopNode->childNodes;
                        
                        /* copy all children of current TopNode */
                        for($CurNode=0; $CurNode<$TopChildren->length; $CurNode++)
                            {
                                $LowerNode->appendChild($this->Dom->importNode($TopChildren->item($CurNode), true));
                            }
                        
                        $UpperNode->replaceChild($LowerNode, $TopNode);
                        $TopNode = $LowerNode;
                    }
                
                return $TopNode;
            }
        
        /**
         * convert the XML and XSL into XHTML and display it
         *
         * @param bool whether to display raw XML data or the converted XHTML
         * @param string/array/bool if a string or array is passed Render will send this information to the GenerateXsl
         *                  method to generate the full xsl stylesheet (so that we can cut down on syntax length when coding
         *                  with DOMi. the other option for this is false, signifying that the GenerateXsl step has already
         *                  taken place prior to calling this funciton.
         * @retval void
         */
        public function Render($StylesheetList = false, $Mode = self::RENDER_HTML)
            {
                if ($StylesheetList !== false)
                    {
                        $this->GenerateXsl($StylesheetList);
                    }
                
                /* remove disabled xpaths from the current XML tree before rendering */
                $this->DisabledXPathsRemove();
                
                $Output = null;
                switch($Mode)
                    {
                        case self::RENDER_XML:
                        $Output = $this->Dom->saveXML();
                        break;
                        
                        case self::RENDER_HTML:
                        $Output = $this->Xslt->transformToXML($this->Dom);
                        break;
                        
                        case self::RENDER_VIEW:
                        $Output = $this->Xslt->transformToXML($this->Dom);
                        break;
                    }
                $this->DocTypeChecker($Output, $Mode);
                echo $Output;
            }
        
        /**
         * Cycle through the list of all Disabled XPaths. If any of them are in the tree, remove them.
         */
        protected function DisabledXPathsRemove()
            {
                foreach($this->DisabledXPaths as $XPath)
                    {
                        $Matches = $this->query($XPath);
                        for ($Ind = 0; $Ind < $Matches->length; $Ind++)
                            {
                                $Match = $Matches->item($Ind);
                                $Match->parentNode->removeChild($Match);
                            }
                    }
            }
        
        /**
         * Checks to see if this is the only output for the entire page. If it is it allows the doctype 
         * declaration at the top of the output; however, if it is not, then it strips that first line (doctype) 
         * from the output before it is dumped.
         * 
         * @param string string value of the translated xml tree
         * @retval string translated xml tree in string format minus doctype if needed
         */
        protected function DocTypeChecker(&$Output, $Mode)
            {
                $OutputAlready = false;
                $Buffer = ob_get_contents();
                if ($Buffer && trim($Buffer) != "")
                    {
                        $Buffer = true;
                    }
                
                if (headers_sent())
                    {
                        $Buffer = true;
                    }
                
                if ($Buffer)
                    {
                        $this->StripDocType($Output);
                    }
                else
                    {
                        switch($Mode)
                            {
                                case self::RENDER_XML:
                                case self::RENDER_VIEW:
                                header('Content-type: text/xml');
                                break;
                            }
                    }
            }
        
        /**
         * actually does the stripping of the doctype if determined that it should be stripped from the 
         * DocTypeChecker method.
         * 
         * @param string string value of the translated xml tree
         * @retval string translated xml tree in string format minus doctype if needed
         */
        protected function StripDocType(&$Output)
            {
                $Regex = '/((<\!.*?>)|(<?.*?\?>))/';
                $Output = preg_replace($Regex, '', $Output);
            }
        
        /**
         * dynamically generates an xsl document with an include directive for each of the templates provided
         *
         * @param array array with each element being the filename for an xsl template that is to be included in the render
         * @param bool flag to determine whether this function should automatically make the generated stylesheet the 
         *             stylesheet for the output.
         * @retval domdocument the new domdocument that contains the XSL document with includes to each requested template
         */
        public function GenerateXsl($Templates, $ImportStylesheet = true)
            {
                if (is_string($Templates) && trim($Templates) != "")
                    {
                        $Templates = array($Templates);
                    }
                
                // set up the domdocument to handle an xsl stylesheet configuration
                $XSL = new DOMDocument('1.0', 'iso-8859-1');
                $Stylesheet = $XSL->createElementNS('http://www.w3.org/1999/XSL/Transform', 'xsl:stylesheet');
                $Stylesheet->setAttribute('version', '1.0');
                $Stylesheet->setAttribute('xmlns:xsl', 'http://www.w3.org/1999/XSL/Transform');
                $XSL->appendChild($Stylesheet);
                
                // create an include node for each template that is being requested
                foreach($Templates as $Template)
                    {
                        $Include = $XSL->createElementNS('http://www.w3.org/1999/XSL/Transform', 'xsl:include');
                        $Include->setAttribute('href', $Template);
                        $Stylesheet->appendChild($Include);
                    }
                
                if ($ImportStylesheet)
                    {
                        $this->importStylesheet($XSL);
                    }
                
                return $XSL;
            }
        
        /**
         * add to the namespaces array a new namespace that may be added to the xml tree
         * 
         * @param string the prefix of the namespace, for instance - xsl:value-of uses the xsl namespace
         * @param string url identifying the namespace declaration
         * @retval bool whether or not  the namespace was added properly
         */
        public function AddNamespace($Prefix, $Url)
            {
                if(preg_match(self::REGEX_NS_PREFIX, $Prefix) && preg_match(self::REGEX_URL, $Url))
                    {
                        $this->Namespace[] = array('prefix'=>$Prefix, 'url'=>$Url);
                        $this->MainNode->setAttribute('xmlns:'.$Prefix, $Url);
                        return true;
                    }
                else
                    {
                        trigger_error('unable to add namespace', E_USER_WARNING);
                        return false;
                    }
            }
        
        /**
         * take a string that is an attempted prefix and check whether or not it can be created
         *
         * @param string the prefix that is attempting to be created
         * @retval bool whether or not the prefix can be created
         */
        public function ValidPrefix($Prefix)
            {
                $Valid = false;
                
                // check to see if the prefix is perfectly fine as it is
                if(preg_match('/^[a-zA-Z][-a-zA-Z0-9_.]*$/', $Prefix))
                    {
                        $Valid = true;
                    }
                // find out if the prefix contains a semicolon, which would indicate a namespaced prefix
                elseif(strpos($Prefix, ':'))
                    {
                        $Valid = $this->ValidNamespacedPrefix($Prefix);
                    }
                
                return $Valid;
            }
        
        /**
         * check to see if a string is a valid namespace prefix
         *
         * @param string the prefix that is being checked
         * @retval bool whether or not the prefix is valid
         */
        private function ValidNamespacedPrefix($Prefix)
            {
                $PrefixChunks = explode(':', $Prefix);
                
                $Valid = true;
                
                if(count($PrefixChunks) != 2)
                    {
                        $Valid = false;
                    }
                elseif(!$this->ValidNamespace($PrefixChunks[0]))
                    {
                        $Valid = false;
                    }
                elseif(!preg_match(self::REGEX_PREFIX, $PrefixChunks[1]))
                    {
                        $Valid = false;
                    }
                
                return $Valid;
            }
        
        /**
         * check to see if a provided namespace has been added to the namespace list
         *
         * @param string the namespace that is being checked
         * @retval bool whether or not the namespace is valid
         */
        private function ValidNamespace($Ns)
            {
                /// @todo make a better function that doesn't go in more than one scope to determine whether or not the namespace is valid
                
                $Valid = array();
                
                foreach($this->Namespace as $Key=>$Namespace)
                    {
                        $Valid[$Key] = $Namespace['prefix'] == $Ns;
                    }
                
                if(is_int($Locate = array_search(true, $Valid)))
                    {
                        $Response = $this->Namespace[$Locate]['url'];
                    }
                else
                    {
                        $Response = false;
                    }
                
                return $Response;
            }
        
        /**
         * Removes any renegade NameSpaces. Allows for removing a specific NameSpace by passing the 'name' of the NameSpace in the 
         * $NameSpaceName parameter, or by default it removes all NameSpaces across the entire document.
         * 
         * @param string a regex style string without delimiters, specifying the pattern for the namespace to be removed. defaults to '.*?'
         */
        
        public function RemoveNamespace($NameSpaceName=false)
            {
                if (!$NameSpaceName)
                    {
                        $regex = '/ xmlns(:[^=]+)?=".*?"/';
                    }
                else
                    {
                        $regex = '/ xmlns:'.$NameSpaceName.'=(\'|").*?(\'|")/';
                    }
                $FixedXml = preg_replace($regex,'',$this->Dom->saveXML());
                $this->Dom->loadXML(preg_replace($regex,'',$this->Dom->saveXML()));    
            }
        
        /**
         * Adds to the current list of disabled XPaths additional XPaths described in the new list of XPaths passed to the 
         * method in array of strings format.
         *
         * @param array|string an array of strings where the strings describe one XPath. if this is just a string, it will be
         *                  added a one element array and treated as such.
         */
        public function AddDisabledXPaths($List)
            {
                if (is_string($List))
                    {
                        $List = array($List);
                    }
                
                array_splice($this->DisabledXPaths, 0, 0, $List);
            }
        
        /**
         * Removes any described XPaths from the list of XPaths to be filtered out.
         * 
         * @param array|string list of XPaths to be removed from the list of XPaths if they exist. in the case of this param
         *                  being passed as a string, it will be converted to a one element array and treated as such.
         */
        public function RemoveDisabledXPaths($List)
            {
                if (is_string($List))
                    {
                        $List = array($List);
                    }
                
                foreach($List as $Xpath)
                    {
                        $Location = array_search($Xpath, $this->DisabledXPaths);
                        if ($Location !== false)
                            {
                                unset($this->DisabledXPaths[$Location]);
                            }
                    }
            }
        
        /**
         * Adds to the current list of disabled Prefixes additional Prefixes described in the new list of Prefixes passed to the 
         * method in array of strings format.
         *
         * @param array|string an array of strings where the strings contain one Prefix. if this is just a string, it will be
         *                  added a one element array and treated as such.
         */
        public function AddDisabledPrefixes($List)
            {
                if (is_string($List))
                    {
                        $List = array($List);
                    }
                
                foreach ($List as &$Item)
                    {
                        $Item = strtolower($Item);
                    }
                
                array_splice($this->DisabledPrefixes, 0, 0, $List);
            }
        
        /**
         * Removes any Prefixes from the list of Prefixes to be filtered out.
         * 
         * @param array|string list of XPaths to be removed from the list of Prefixes if they exist. in the case of this param
         *                  being passed as a string, it will be converted to a one element array and treated as such.
         */
        public function RemoveDisabledPrefixes($List)
            {
                if (is_string($List))
                    {
                        $List = array($List);
                    }
                
                foreach ($List as &$Item)
                    {
                        $Item = strtolower($Item);
                    }
                
                foreach($List as $Xpath)
                    {
                        $Location = array_search($Xpath, $this->DisabledPrefixes);
                        if ($Location !== false)
                            {
                                unset($this->DisabledPrefixes[$Location]);
                            }
                    }
            }

        /**
         * Take a file name and check to see if the file is a valid XML file
         *
         * @param string the file to be checked
         */
        public function IsValidXml($Data)
            {
                @$Dom = simplexml_load_string(file_get_contents($Data));
                
                $Return = $Dom === false ? false : true;

                return $Return;
            }
    }

?>
