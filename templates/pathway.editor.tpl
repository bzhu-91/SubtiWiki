<div id="editor">{{:map}}</div>
<div id="top-menu-bar">
    <span>
        <button id="btn-new" title="Create a new pathway">New</button>
        <button id="btn-rename" title="Rename the current pathway">Rename</button>
        <button id="btn-save" title="Save the changes">Save</button>
        <button id="btn-add-reaction" title="Add a reaction in the database to the map">Add reaction to map</button>
        <button onclick="window.open('reaction/editor?pathway={{:id}}')" title="Add a reaction to the database">Add reaction to the pathway</button>
        <button id="btn-help" title="Show help document">Help</button>
    </span>
    <span id="span-title" style="float:right"><select id="select-pathway"></select></span>
</div>
<div id="panel-add-reaction" title="Add a reaction">
    <form id="form-search-reaction">
        <p><input name="reaction" placeholder="chemical name ..."/><input type="submit" /></p>
    </form>
    <ul id="container-reactions"></ul>
</div>
<div id="menu-membrane" class="popup">
    <p><b>Change the size of the membrane</b></p>
    <div>
        <label>Width: </label>
        <div id="btn-membrane-width-minus" class="button">&nbsp;-&nbsp;</div>
        <div id="btn-membrane-width-plus" class="button">&nbsp;+&nbsp;</div>
    </div>
    <br>
    <div>
        <label>Height: </label>
        <div id="btn-membrane-height-minus" class="button">&nbsp;-&nbsp;</div>
        <div id="btn-membrane-height-plus" class="button">&nbsp;+&nbsp;</div>
    </div>
</div>
<ul id="menu-reaction" class="menu">
    <li><div id="btn-select-connected-components">Select all connected</div></li>
    <li><div id="btn-reverse-sides">Reverse sides</div></li>
    <li><div id="btn-change-layout">Change layout (v/h)</div></li>
    <li><div id="btn-remove-reaction">Remove</div></li>
</ul>
<ul id="menu-metabolite" class="menu">
    <li><div id="btn-lock-metabolites">Lock the stacking elements</div></li>
    <li><div id="btn-unlock-metabolites">Unlock the stacking elements</div></li>
    <li><div>Related reactions</div>
        <ul id="menu-metabolite-suggestions"></ul>
    </li>
</ul>
<form action="pathway?id={{:id}}" type="ajax" id="form-rename" method="put">
    <p><b>Rename Pathway</b></p>
    <input name="title" value="{{:title}}" placeholder="pathway name"/>
    <input type="submit" />
</form>
<form action="pathway" type="ajax" id="form-new" method="post">
    <p><b>New Pathway</b></p>
    <input name="title" value="" placeholder="pathway name"/>
    <input type="submit" />
</form>
<div id="help-info">
    <h2 style="color:Indianred; line-height:3em">How to edit? It is easy!</h2>
    <div>
        <p style="font-weight:bold; font-size:larger">1. Moving around</p>
        <svg style="height: 170px">
            <g>
                <g class="protein" transform="matrix(1,0,0,1,40,20)" id="MPN430" uuid="3af4d301-b422-f005-8a0e-e5e4761b231b">
                    <rect class="_protein_rect" x="-35.1171875" y="-19.375" width="70.734375" height="39.25"></rect>
                    <text class="_protein_text" pointer-events="none" x="-15.1171875" y="9.375">Gap</text>
                </g>
                <g class="protein" transform="matrix(1,0,0,1,120,20)" id="MPN430" uuid="3af4d301-b422-f005-8a0e-e5e4761b231b">
                    <rect class="_protein_rect" x="-35.1171875" y="-19.375" width="70.734375" height="39.25" style="stroke-width: 2"></rect>
                    <text class="_protein_text" pointer-events="none" x="-15.1171875" y="9.375">Gap</text>
                </g>
                <text x="200" y="20">Click on an element to select it</text>
                <rect style="stroke: #0099cc; stroke-width:2px; stroke-dasharray: 10 5; fill: transparent" class="background" width="80" height="30" x="10" y="60"></rect>
                <text x="200" y="80">Click on the dashline box to select a reaction</text>
                <g class="protein" transform="matrix(1,0,0,1,40,130)" id="MPN430" uuid="3af4d301-b422-f005-8a0e-e5e4761b231b">
                    <rect class="_protein_rect" x="-35.1171875" y="-19.375" width="70.734375" height="39.25" style="stroke-width: 2"></rect>
                    <text class="_protein_text" pointer-events="none" x="-15.1171875" y="9.375">Gap</text>
                </g>
                <rect style="stroke: #0099cc; stroke-width:2px; stroke-dasharray: 10 5; fill: transparent" class="background" width="80" height="30" x="90" y="115"></rect>
                <text x="200" y="140">Drag to move the selected element</text>
            </g>
        </svg>
        <p style="font-weight:bold; font-size:larger">2. Add a reaction</p>
        <p>Select the "Add reation" button on the top menu bar</p>
        <p>Right click on a metabolite to include an associated reaction (from a drop down menu)</p>
        <p style="font-weight:bold; font-size:larger">3. Link two reactions</p>
        <p>Move the common metabolites overlapping each other</p>
        <p>Right click on the stacked metabolites</p>
        <p>Click "Lock the stacking elements" on the drop down menu</p>
        <p style="font-weight:bold; font-size:larger">3. Change the layout of the reaction</p>
        <p>Right click on the reaction (inside the blue dashed box)</p>
        <p>Select "reverse sides" to swap the left and right hand side</p>
        <p>Select "change layout" to toggle between horizontal or veritcal layout</p>
</div>