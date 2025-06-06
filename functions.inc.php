<?php

function advcallspy_check_extensions($exten=true) {
    return FreePBX::create()->Advcallspy()->checkExtMap($exten);
}