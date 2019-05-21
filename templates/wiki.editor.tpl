<form action="wiki" method="{{:method}}" type="ajax" class="quill">
    <div class="table-row">
        <label>Title: </label>
        <input name="title" value="{{:title}}" />
    </div>
    <div class="table-row">
        <label>Content: </label>
        <div>
            <p><i>Hint: to add gene/protein/category, hit # symbol then type in gene/protein/category name</i></p>
            <textarea name="article">{{:article}}</textarea>
        </div>
    </div>
<input type="hidden" name="id" value="{{:id}}" />
<p style="text-align:right">
    <a class="button delBtn" id="{{:id}}" target="wiki" style="float:left; margin-left:0">Delete</a>
    <input type="submit" />
</p>
</form>
{{jsvars:vars}}
