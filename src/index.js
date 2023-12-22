import React from "react";
import ReactDom from 'react-dom';
import App from './App';

document.addEventListener( 'DOMContentLoaded', function () {
    let rechartContainer = document.getElementById( 'rechart-container' );

    if ( typeof rechartContainer !== "undefined" && rechartContainer !== null ) {
        ReactDom.render( <App />, rechartContainer );
    }
} );
