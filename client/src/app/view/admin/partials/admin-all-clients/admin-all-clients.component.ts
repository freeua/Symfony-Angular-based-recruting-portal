import { Component, ElementRef, OnInit, ViewChild } from '@angular/core';
import { BusinessApprove } from '../../../../../entities/models-admin';
import { AdminService } from '../../../../services/admin.service';
import { SharedService } from '../../../../services/shared.service';
import { ToastrService } from 'ngx-toastr';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { Angular5Csv } from 'angular5-csv/Angular5-csv';

@Component({
  selector: 'app-admin-all-clients',
  templateUrl: './admin-all-clients.component.html',
  styleUrls: ['./admin-all-clients.component.scss']
})
export class AdminAllClientsComponent implements OnInit {

  @ViewChild('content') private content : ElementRef;

  public businessList = Array<BusinessApprove>();
  public modalActiveClose: any;
  public selectedBusinessId: number;
  public search = '';
  public preloaderPage = true;

  public paginationLoader = false;
  public pagination = 1;
  public loadMoreCheck = true;
  public deleteCheck = false;

  constructor(
    private readonly _adminService: AdminService,
    private readonly _sharedService: SharedService,
    private readonly _toastr: ToastrService,
    private readonly _modalService: NgbModal
  ) {
    this._sharedService.checkSidebar = false;
  }

  ngOnInit() {
    window.scrollTo(0, 0);
    this.getAllBusinessList(this.search, false);
  }

  /**
   * Export CSV file
   * @return {Promise<void>}
   */
  public async exportDataCSV(search, csv): Promise<void>{

    const params = {
      search: search,
      page: 1,
      csv: csv
    };

    try {
      const response = await this._adminService.getAllBusinessList(params);

      const options = {
        showLabels: true,
        headers: ['Name', 'Company', 'Email' , 'Tel Number', 'Referring Agent', 'Active']
      };

      new Angular5Csv(response, 'All clients', options);
    }
    catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Reset Array
   */
  public resetArrayPagination(): void{
    this.businessList = [];
    this.pagination = 1;
  }

  /**
   * Load pagination
   */
  public async loadPagination(search): Promise<void> {

    this.pagination++;
    this.paginationLoader = true;
    this.getAllBusinessList(search, false);
  }

  /**
   * Get all business profiles
   * @param search {string}
   * @param csv {boolean}
   * @return {Promise<void>}
   */
  public async getAllBusinessList(search: string, csv): Promise<void> {

    const params = {
      search: search,
      page: this.pagination,
      csv: csv
    };

    try {
      const response = await this._adminService.getAllBusinessList(params);

      response.items.forEach((item) => {
        this.businessList.push(item);
      });

      if (response.pagination.total_count === this.businessList.length) {
        this.loadMoreCheck = false;
      }
      else if (response.pagination.total_count !== this.businessList.length){
        this.loadMoreCheck = true;
      }

      this.preloaderPage = false;
      this.paginationLoader = false;
    }
    catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Delete business profile
   * @param id {number}
   * @return {Promise<void>}
   */
  public async deleteBusinessProfile(id): Promise<void> {
    try {
      await this._adminService.deleteBusinessProfile(id);

      this.businessList = this.businessList.filter((listElement) => listElement.id !== id);
      this.modalActiveClose.dismiss();
      this._sharedService.sidebarAdminBadges.clientAll--;
      this._toastr.success('Client has been deleted');
    }
    catch (err) {
      this._sharedService.showRequestErrors(err);
    }
  }

  /**
   * Update business status
   * @param id {number}
   * @param enabled {boolean}
   * @return {Promise<void>}
   */
  public async updateBusinessStatus(id: number, enabled: boolean): Promise<void> {
    enabled = !enabled;
    try {
      await this._adminService.updateBusinessStatus(id, enabled);
      this._toastr.success('Client status has been updated');
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

  /**
   * Managed modal
   * @param content {any} - content to be shown in popup
   */
  public openVerticallyCenter(content: any) {
    this.modalActiveClose = this._modalService.open(content, { centered: true, 'size': 'sm' });
  }

}
