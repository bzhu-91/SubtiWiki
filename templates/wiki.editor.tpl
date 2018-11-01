<form action="wiki" method="{{:method}}" type="ajax" class="quill">
<p>
    <p>
        <label>Title: </label>
        <input name="title" value="{{:title}}" />
    </p>
    <textarea name="article">{{:article}}</textarea>
</p>
<input type="hidden" name="id" value="{{:id}}" />
<p style="text-align:right">
    <a class="button delBtn" id="{{:id}}" target="wiki" style="float:left; margin-left:0">Delete</a>
    <input type="submit" />
</p>
</form>
{{jsvars:vars}}
