app.controller('#ID#',function($rootScope, $scope, $uibModal, $http, $filter, $timeout, prompt, seq, CONFIG, MESSAGE){

	$scope.config = {
		title:"#TITLE#",
		idField:"#ID_FIELD#",
		listApi:"#LIST_API#",
		createApi:"#CREATE_API#",
		removeApi:"#REMOVE_API#",
		updateApi:"#UPDATE_API#"
	};

	$scope.opt = {
		currentPage:1,
		num:30,
		list:[],
		total:0
	}

	$scope.reload = function() {
		
		var param = {
			num:$scope.opt.num,
			page:($scope.opt.currentPage-1)
		};

		$http.post(CONFIG.API_BASE_URL + $scope.config.listApi, param).success(function(resp){
			$scope.opt.list = resp.dataList.map(function(e){
				//convert date here..
				return e;
			});			
			$scope.opt.total = resp.total;
		});

	}

	$scope.edit = function(item) {

		$uibModal.open({
			size:"lg",
			resolve:{ 
				item:function(){ return angular.copy(item); },
				config:function(){ return $scope.config; }
			},
			templateUrl:"#MODAL_PREFIX#-edit-modal.html",
			controller:function($scope, $http, $uibModalInstance, CONFIG, item, config){
				$scope.item = item;
				$scope.config = config;
				$scope.submit = function(){
					$http.post(CONFIG.API_BASE_URL + $scope.config.updateApi, angular.copy($scope.item)).success(function(resp){
						if(resp.success){
							alert("수정 되었습니다.");
							$uibModalInstance.close();
						}else{
							alert("수정 실패 : "+resp.error);
						}
					})
				}
				$scope.cancel = function(){
					$uibModalInstance.dismiss("cancel");
				}

			}
		}).result.then(function(){
			$scope.reload();
		})

	}

	$scope.add = function() {
		$uibModal.open({
			size:"lg",
			resolve:{ 
				item:function(){ return null; },
				config:function(){ return $scope.config; }
			},
			templateUrl:"#MODAL_PREFIX#-add-modal.html",
			controller:function($scope, $http, $uibModalInstance, CONFIG, item, config){
				$scope.item = item || {};
				$scope.config = config;
				$scope.submit = function(){
					$http.post(CONFIG.API_BASE_URL + $scope.config.createApi, angular.copy($scope.item)).success(function(resp){
						if(resp.success){
							alert("등록 되었습니다.");
							$uibModalInstance.close();
						}else{
							alert("등록 실패 : "+resp.error);
						}
					})
				}
				$scope.cancel = function(){
					$uibModalInstance.dismiss("cancel");
				}

			}
		}).result.then(function(){
			$scope.reload();
		})
	}

	$scope.removeSelected = function() {

		if(confirm("정말 선택한 것을 삭제합니까?")){

			var selectedList = $scope.opt.list.filter(function(e){ return e.selected; });

			var tasks =  selectedList.map(function(item){
				var param = {};
				param[$scope.config.idField] = item[$scope.config.idField]
				return function() { return $http.post(CONFIG.API_BASE_URL + $scope.config.removeApi, param); };655
			});

			seq.run(tasks).then(function(){
				prompt({title:"삭제완료", message:"선택항목을 모두제거하였습니다."});
				$scope.reload();
			});

		}
	}

	$scope.selectAll = function() {
		for(var k in $scope.opt.list){
			var item = $scope.opt.list[k];
			item.selected = !item.selected;
		}
	}

	
	$scope.reload();

})