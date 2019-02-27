import { Component, OnInit } from '@angular/core';
import { CandidateApprove } from '../../../../../entities/models-admin';
import { ToastrService } from 'ngx-toastr';
import { NgbModal } from '@ng-bootstrap/ng-bootstrap';
import { AdminService } from '../../../../services/admin.service';
import { SharedService } from '../../../../services/shared.service';

@Component({
  selector: 'app-admin-new-candidates',
  templateUrl: './admin-new-candidates.component.html',
  styleUrls: ['./admin-new-candidates.component.scss']
})
export class AdminNewCandidatesComponent implements OnInit {

  public approveCandidateList = Array<CandidateApprove>();

  public modalActiveClose: any;

  public selectedId: number;
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
    private readonly _sharedService: SharedService,

  ) {
    this._sharedService.checkSidebar = false;
  }

  ngOnInit() {
    window.scrollTo(0, 0);
    this.getApproveCandidate();
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
   * Hide articles firm
   * @param elem
   */
  public hideArticlesFirm(elem): void {
    let nextSibling = elem.nextSibling;
    while(nextSibling && nextSibling.nodeType != 1) {
      nextSibling = nextSibling.nextSibling
    }
    nextSibling.style.opacity = 0;
  }

  /**
   * Load pagination
   */
  public async loadPagination(): Promise<void> {

    this.pagination++;
    this.paginationLoader = true;
    this.getApproveCandidate();
  }

  /**
   * Get approve candidate
   * @return {Promise<void>}
   */
  public async getApproveCandidate(): Promise<void> {
    const response = await this._adminService.getApproveCandidate(this.pagination);

    response.items.forEach((item) => {
      this.approveCandidateList.push(item);
    });

    if (response.pagination.total_count === this.approveCandidateList.length) {
      this.loadMoreCheck = false;
    }
    else if (response.pagination.total_count !== this.approveCandidateList.length){
      this.loadMoreCheck = true;
    }
    this.paginationLoader = false;

    this.preloaderPage = false;
  }

  /**
   * Managed modal
   * @param content {any} - content to be shown in popup
   * @param id {number} - job id to be used for fetching data and showing in popup
   */
  public openVerticallyCentered(content: any,  id: number) {
    this.modalActiveClose = this._modalService.open(content, { centered: true, 'size': 'lg' });
    this.selectedId = id;
  }

}
