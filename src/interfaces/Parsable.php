<?php

namespace AKL\Interfaces;

interface Parsable{
  function set( $orig, $action, $opts );

  function capture( $string );

  function setTokens( $string );
}
