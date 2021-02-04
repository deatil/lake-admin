"use strict";

layui.define(["layer"], function (exprots) {
    var $ = layui.jquery;
    
    // 获取选中节点的id
    function getCheckedIds(data) {
        var id = "";
        $(data).each(function (index, item) {
            if (id != "") {
                id = id + "," + item.id;
            }
            else {
                id = item.id;
            }
            var i = getCheckedIds(item.children);
            if (i != "") {
                id = id + "," + i;
            }
        });
        return id;
    }
    
    // 格式化选中节点的id
    function formatCheckedIds(ids, data) {
        var checkedIds = "";
        $(data).each(function (index, item) {
            var id = item.id;
            if (item.children == undefined 
                && $.inArray(id, ids) != -1 
            ) {
                if (checkedIds != "") {
                    checkedIds = checkedIds + "," + id;
                } else {
                    checkedIds = id;
                }
            }
            
            if (item.children != undefined) {
                var childCheckedIds = formatCheckedIds(ids, item.children);
                if (childCheckedIds != "") {
                    checkedIds = checkedIds + "," + childCheckedIds;
                }
            }
        });
        
        var checkedIdArr = checkedIds.split(',');
        checkedIdArr = $.grep(checkedIdArr, function(n, i) {
            return n;
        },false);
        
        return checkedIdArr;
    }
    
    var treeTool = {
        getCheckedIds: getCheckedIds,
        formatCheckedIds: formatCheckedIds,
    };


    exprots("treeTool", treeTool);
});
