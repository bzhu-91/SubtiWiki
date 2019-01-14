<div class="box">
    <h3>Complex</h3>
    <div>{{:message}}</div>
    <form action='complex' method='{{:method}}' type='ajax' style="background: #eee; padding:10px">
        <p>
            <label>Title</label>
            <input type='text' name='title' value='{{:title}}' style="width: 300px"/>
        </p>
       <p>
           <label>Description</label>
           <input type="text" name="description" style="width: 400px" value="{{:description}}" /> 
       </p>
        <p style="text-align: right">
            <input type="submit" />
        </p>
        <input type="hidden" name="id" value="{{:id}}" />
    </form>
    <form action="complex" method="delete" type="ajax"  style="float:left;top:-50px;left:5px;position: relative; display: {{:showDelForm}}">
        <input type="hidden" name="id" value="{{:id}}" />
        <input type="submit" class="delBtn" value="Delete" style="background:red"/>
    </form>
    <p style="clear:both"></p>
    <div style="display: {{:showMembers}}">
        <h3>Complex Members</h3>
        <div style="background: #eee; padding: 10px; ">
            {{complexMember:members}}
            <form action="complex/member" method="post" type="ajax" >
                <p>
                    <label>Coefficient: </label>
                    <input type="number" name="coefficient" />
                    <label>Member Type: </label>
                    <select name="type">
                        <option>Please select</option>
                        <option value="metabolite">Metabolite</option>
                        <option value="protein">Protein</option>
                    </select>&nbsp;
                    <label>Member: </label>
                    <input type="text" name="title" />
                    <label>Modification: </label>
                    <input type="text" name="modification" />
                    <input type="submit" />
                    <input type="hidden" name="complex" value="{{:id}}" />
                </p>
            </form>
        </div>
    </div>
</div>