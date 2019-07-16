<div class="box">
    <h3>Complex</h3>
    <div>{{:message}}</div>
    <form action='complex' method='{{:method}}' type='ajax' >
        <p class="table-row">
            <label>Title</label>
            <input type='text' name='title' value='{{:title}}' style="width: 300px"/>
        </p>
       <p class="table-row">
           <label>Description</label>
           <input type="text" name="description" style="width: 400px" value="{{:description}}" /> 
       </p>
        <p style="text-align: right">
            <input type="submit" />
        </p>
        <input type="hidden" name="id" value="{{:id}}" />
    </form>
    <form action="complex" method="delete" type="ajax"  style="float:left;top:-50px;position: relative; display: {{:showDelForm}}">
        <input type="hidden" name="id" value="{{:id}}" />
        <input type="submit" class="delBtn" value="Delete" style="background:red"/>
    </form>
    <p style="clear:both"></p>
    <div style="display: {{:showMembers}}">
        <h3>Complex Members</h3>
        <table class="common">
            <tr>
                <th>Coefficient</th><th>Type</th><th>Member name</th><th>Modification</th><th>Operation</th>
            </tr>
            {{complexMember:members}}
            <tr action="complex/member" method="post" class="form" id="add-complex-member">
                <td>
                    <input type="number" name="coefficient" value="1"/>
                </td>
                <td>
                    <select name="type">
                        <option value="metabolite">Metabolite</option>
                        <option value="protein">Protein</option>
                        <option value="DNA">DNA</option>
                        <option value="RNA">RNA</option>
                    </select>
                </td>
                <td>
                    <input type="metabolite" name="member" />
                </td>
                <td>
                    <input type="text" name="modification" />
                </td>
                <td>
                    <input type="submit" />
                    <input type="hidden" name="complex" value="{{:id}}" />
                </td>
            </tr>
        </table>
    </div>
</div>