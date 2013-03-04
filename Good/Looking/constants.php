<?php

namespace Good\Looking;

class ErrorLevels
{
    const low    = 0;		// like an undeclared variable, invisible on page, visible in source
    const medium = 1;	    // visible on screen
    const high   = 2;		// visible in javascript popup and source
    const fatal  = 3;	    // script is terminated, visible in popup and on screen
}


class CompilerMapModes
{
    const plain  = 0; 
    const script = 1;
    const layer  = 2;
    const branch = 3;
}

class CompilerLayerTypes
{
    const toplevel           = 0;
    const contStrucStarting  = 1;
    const contStrucBranching = 2;
}

class CompilerOutputTypes
{
    const text = 0;
    const php  = 1;
}
?>