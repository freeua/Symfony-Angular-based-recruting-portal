import { Component, OnInit } from '@angular/core';
import { AdminService } from '../../../../services/admin.service';
import { BusinessApprove } from '../../../../../entities/models-admin';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { ToastrService } from 'ngx-toastr';
import { SharedService } from '../../../../services/shared.service';

@Component({
  selector: 'app-admin-new-clients',
  templateUrl: './admin-new-clients.component.html',
  styleUrls: ['./admin-new-clients.component.scss']
})
export class AdminNewClientsComponent implements OnInit {

  public approveBusinessList = Array<BusinessApprove>();

  public modalActiveClose: any;

  public selectedBusinessId: number;
  public preloaderPage = true;

  public paginationLoader = false;
  public pagination = 1;
  public loadMoreCheck = true;

  public confirmFunction: string;
  public confirmData: any;
  public confirmStatus: any;
  public confirmArray: any;

  constructor(
    private readonly _adminService: AdminService,
    private readonly _modalService: NgbModal,
    private readonly _toastr: ToastrService,
    private readonly _sharedService: SharedService
  ) {
    this._sharedService.checkSidebar = false;
  }

  ngOnInit() {
    window.scrollTo(0, 0);
    this.getApproveBusiness();
  }

  /**
   * Open confirm popup
   * @param content
   * @param confirmArray
   * @param nameFunction
   * @param data
   * @param status
   */
  public openConfirm(content: any, confirmArray, nameFunction, data, status): void {
    this.modalActiveClose = this._modalService.open(content, { centered: true, 'size': 'sm', windowClass: 'width-min' });
    this.confirmFunction = nameFunction;
    this.confirmData = data;
    this.confirmStatus = status;
    this.confirmArray = confirmArray;
  }

  /**
   * Load pagination
   */
  public async loadPagination(): Promise<void> {

    this.pagination++;
    this.paginationLoader = true;
    this.getApproveBusiness();
  }

  /**
   * Get approve business
   * @return {Promise<void>}
   */
  public async getApproveBusiness(): Promise<void> {
    try {
      const response = await this._adminService.getApproveBusiness(this.pagination);

      response.items.forEach((item) => {
        this.approveBusinessList.push(item);
      });

      if (response.pagination.total_count === this.approveBusinessList.length) {
        this.loadMoreCheck = false;
      }
      else if (response.pagination.total_count !== this.approveBusinessList.length){
        this.loadMoreCheck = true;
      }
      this.paginationLoader = false;

      this.preloaderPage = false;
    }
    catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Managed modal
   * @param content {any} - content to be shown in popup
   * @param id {number} - job id to be used for fetching data and showing in popup
   */
  public openVerticallyCentered(content: any,  id: number) {
    this.modalActiveClose = this._modalService.open(content, { centered: true, 'size': 'lg' });
    this.selectedBusinessId = id;
  }
}
