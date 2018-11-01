<div id="browser-container">
    <div id="network-container">{{:map}}</div>
    <div id="search-block">
        <form id="search">
            <p><input type="text" name="geneName" placeholder="gene name" /><input type="submit" /></p>
        </form>
    </div>
    <div id="control-block">
        <img src="img/settings_1.png" id="collapsed" style="display: none"/>
        <div id="full" style="display: block">
            <p>
                <label>All pathways</label>
                <select id="all-pathways"></select>
                <button id="full-screen">Full screen</button>
            </p>
            <p>
                <label>All enzymes:</label>
                <select id="all-proteins" style="width:150px;" class="highlight"></select>
                <label>All metabolites:</label>
                <select id="all-metabolites" style="width:150px" class="highlight"></select>
                <button id="clear-highlight">Clear highlight</button>
            </p>
            <p>
                <span id="omics-data-select-container" style="line-height: 2em"></span>
                <button id="clear-omic-data">Clear omics data</button>
            </p>
            <img src="img/close.png" style="float: right; cursor: pointer;" id="closePanel" />
            <p style="clear: both;"></p>
        </div><!-- full -->
    </div>
    <div id="legend"></div>
    <div id="popup">
        <div id="info-box"></div>
        <img src="img/close.png" id="popup-cancel" class="icon" />
    </div> <!-- popup -->
</div>
{{jsvars:vars}}
